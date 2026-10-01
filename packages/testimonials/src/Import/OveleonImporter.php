<?php

declare(strict_types=1);

namespace Nordwerk\TestimonialsBundle\Import;

use Doctrine\DBAL\Connection;
use Nordwerk\TeasersBundle\Rendering\PublicFiles;

final class OveleonImporter
{
    public function __construct(
        private readonly Connection $connection,
        private readonly PublicFiles $files,
    ) {
    }

    /**
     * @return array{imported: int, skipped: int}
     */
    public function import(int $sourceArchive, int $targetArchive, bool $execute = false): array
    {
        $schema = $this->connection->createSchemaManager();

        if (!$schema->tablesExist(['tl_recommendation', 'tl_recommendation_archive'])) {
            throw new \RuntimeException('Oveleon recommendation tables are missing.');
        }

        $columns = $schema->listTableColumns('tl_recommendation');

        foreach (['id', 'pid', 'author', 'text', 'rating', 'date'] as $field) {
            if (!isset($columns[strtolower($field)])) {
                throw new \RuntimeException('Unsupported Oveleon schema; missing field '.$field.'.');
            }
        }

        if (!$this->connection->fetchOne('SELECT id FROM tl_recommendation_archive WHERE id=?', [$sourceArchive]) || !$this->connection->fetchOne('SELECT id FROM tl_nw_testimonial_archive WHERE id=?', [$targetArchive])) {
            throw new \RuntimeException('Source or target archive does not exist.');
        }

        $result = ['imported' => 0, 'skipped' => 0];
        $this->connection->beginTransaction();

        try {
            foreach ($this->connection->fetchAllAssociative('SELECT * FROM tl_recommendation WHERE pid=? ORDER BY id', [$sourceArchive]) as $row) {
                $key = 'oveleon:'.$sourceArchive.':'.$row['id'];

                if ($this->connection->fetchOne('SELECT id FROM tl_nw_testimonial WHERE importKey=?', [$key])) {
                    ++$result['skipped'];

                    continue;
                }

                $record = $this->map($row, $targetArchive, $key);

                if ($execute) {
                    $this->connection->insert('tl_nw_testimonial', $record);
                }

                ++$result['imported'];
            }

            if ($execute) {
                $this->connection->commit();
            } else {
                $this->connection->rollBack();
            }
        } catch (\Throwable $exception) {
            $this->connection->rollBack();

            throw $exception;
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function map(array $row, int $archive, string $key): array
    {
        $plain = static fn (mixed $value): string => trim(html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5));
        $text = $plain(preg_replace('~<br\s*/?>|</(?:p|div|li)>~i', "\n", (string) $row['text']));
        $name = $plain($row['author']);
        $rating = (string) $row['rating'];

        if ('' === $name || mb_strlen($name) > 255 || '' === $text || mb_strlen($text) > 10000) {
            throw new \RuntimeException('Invalid author or text on Oveleon row '.$row['id'].'. Import rolled back.');
        }

        $image = null;
        $path = (string) ($row['imageUrl'] ?? '');

        if (str_starts_with($path, 'files/') && ($file = $this->files->resolve($path))) {
            $image = $file->uuid;
        }

        $provenance = array_intersect_key($row, array_flip(['id', 'pid', 'title', 'alias', 'author', 'customField', 'location', 'date', 'time', 'rating', 'teaser', 'text', 'imageUrl', 'published', 'verified', 'start', 'stop', 'scope', 'featured', 'cssClass']));

        return [
            'pid' => $archive,
            'tstamp' => time(),
            'date' => max(0, (int) $row['date']),
            'name' => $name,
            'role' => mb_substr($plain($row['customField'] ?? ''), 0, 255),
            'source' => mb_substr($plain($row['location'] ?? ''), 0, 255),
            'text' => $text,
            'stars' => \in_array($rating, ['1', '2', '3', '4', '5'], true) ? (int) $rating : 0,
            'email' => mb_substr((string) ($row['email'] ?? ''), 0, 255),
            'singleSRC' => $image,
            'published' => '',
            'consentedAt' => 0,
            'consentText' => '',
            'reviewNotes' => '',
            'importKey' => $key,
            'provenance' => json_encode($provenance, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
        ];
    }
}

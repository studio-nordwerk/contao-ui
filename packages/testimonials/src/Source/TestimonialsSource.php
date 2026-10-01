<?php

declare(strict_types=1);

namespace Nordwerk\TestimonialsBundle\Source;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Nordwerk\TeasersBundle\Card\Card;
use Nordwerk\TeasersBundle\Query\TeaserQuery;
use Nordwerk\TeasersBundle\Source\TeaserSourceInterface;

final class TestimonialsSource implements TeaserSourceInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function getKey(): string
    {
        return 'testimonials';
    }

    public function getLabel(): string
    {
        return 'nw.testimonials.source';
    }

    public function getArchives(): array
    {
        return array_map('strval', $this->connection->fetchAllKeyValue('SELECT id, title FROM tl_nw_testimonial_archive ORDER BY title'));
    }

    public function fetch(TeaserQuery $query): iterable
    {
        if (!$query->archives) {
            return;
        }

        $order = match ($query->sort) {
            'date_asc' => 't.date ASC',
            'title_asc' => 't.name ASC',
            'stars_desc' => 't.stars DESC',
            default => 't.date DESC',
        };

        foreach ($this->connection->fetchAllAssociative("SELECT t.*, a.verification FROM tl_nw_testimonial t JOIN tl_nw_testimonial_archive a ON a.id=t.pid WHERE t.pid IN (?) AND t.published='1' AND t.date<=? AND t.stars>=? ORDER BY ".$order.', t.id DESC LIMIT '.$query->limit, [$query->archives, time(), $query->minStars], [ArrayParameterType::INTEGER]) as $row) {
            $stars = (int) $row['stars'];
            yield new Card(
                (string) $row['name'],
                (string) $row['text'],
                $row['singleSRC'] ? (string) $row['singleSRC'] : null,
                date: (new \DateTimeImmutable())->setTimestamp((int) $row['date']),
                meta: ['role' => (string) $row['role'], 'source' => (string) $row['source'], 'verification' => (string) $row['verification']],
                stars: $stars >= 1 && $stars <= 5 ? $stars : null,
            );
        }
    }
}

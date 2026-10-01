<?php

declare(strict_types=1);

namespace Nordwerk\TeasersBundle\Source;

use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\NewsBundle\Security\ContaoNewsPermissions;
use Contao\NewsModel;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Nordwerk\TeasersBundle\Card\Card;
use Nordwerk\TeasersBundle\Query\TeaserQuery;
use Symfony\Component\Routing\Exception\ExceptionInterface;

final class NewsSource implements TeaserSourceInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ArchiveAccess $access,
        private readonly ContentUrlGenerator $urls,
    ) {
    }

    public function getKey(): string
    {
        return 'news';
    }

    public function getLabel(): string
    {
        return 'nw.teasers.source.news';
    }

    public function getArchives(): array
    {
        return $this->access->backendChoices(array_map('strval', $this->connection->fetchAllKeyValue('SELECT id, title FROM tl_news_archive ORDER BY title')), ContaoNewsPermissions::USER_CAN_EDIT_ARCHIVE);
    }

    public function fetch(TeaserQuery $query): iterable
    {
        $archives = $this->access->allowed('tl_news_archive', $query->archives);

        if (!$archives) {
            return;
        }

        $now = time();
        $sql = "SELECT n.* FROM tl_news n WHERE n.pid IN (?) AND n.published='1' AND n.date<=? AND (n.start='' OR n.start<=?) AND (n.stop='' OR n.stop>?)";
        $params = [$archives, $now, $now, $now + 60];
        $types = [ArrayParameterType::INTEGER];

        if ($query->categories) {
            if (!$this->connection->createSchemaManager()->tablesExist(['tl_news_categories'])) {
                return;
            }

            $sql .= ' AND EXISTS (SELECT 1 FROM tl_news_categories c WHERE c.news_id=n.id AND c.category_id IN (?))';
            $params[] = $query->categories;
            $types[4] = ArrayParameterType::INTEGER;
        }

        $order = match ($query->sort) {
            'date_asc' => 'n.date ASC',
            'title_asc' => 'n.headline ASC',
            default => 'n.date DESC',
        };

        foreach ($this->connection->fetchAllAssociative($sql.' ORDER BY '.$order.', n.id DESC LIMIT '.$query->limit, $params, $types) as $row) {
            $model = new NewsModel();
            $model->setRow($row);

            try {
                $link = $this->urls->generate($model);
            } catch (ExceptionInterface) {
                $link = null;
            }

            yield new Card(
                html_entity_decode((string) $row['headline'], ENT_QUOTES | ENT_HTML5),
                html_entity_decode(strip_tags((string) $row['teaser']), ENT_QUOTES | ENT_HTML5),
                $row['addImage'] && $row['singleSRC'] ? (string) $row['singleSRC'] : null,
                $link,
                (new \DateTimeImmutable())->setTimestamp((int) $row['date']),
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace Nordwerk\TeasersBundle\Query;

use Contao\StringUtil;

final readonly class TeaserQuery
{
    /**
     * @param list<int> $archives
     * @param list<int> $categories
     */
    public function __construct(
        public array $archives = [],
        public array $categories = [],
        public int $limit = 6,
        public string $sort = 'date_desc',
        public int $minStars = 0,
    ) {
        if ($limit < 1 || $limit > 100 || !\in_array($sort, ['date_desc', 'date_asc', 'title_asc', 'stars_desc'], true) || $minStars < 0 || $minStars > 5) {
            throw new \InvalidArgumentException('Invalid teaser query.');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromData(array $data): self
    {
        $sort = (string) ($data['nwTeaserSort'] ?? 'date_desc');

        return new self(
            self::ids($data['nwTeaserArchives'] ?? null),
            self::ids($data['nwTeaserCategories'] ?? null),
            max(1, min(100, (int) ($data['nwTeaserLimit'] ?? 6))),
            \in_array($sort, ['date_desc', 'date_asc', 'title_asc', 'stars_desc'], true) ? $sort : 'date_desc',
            max(0, min(5, (int) ($data['nwTeaserMinStars'] ?? 0))),
        );
    }

    /**
     * @return list<int>
     */
    private static function ids(mixed $value): array
    {
        return array_values(array_unique(array_filter(array_map('intval', StringUtil::deserialize($value, true)), static fn (int $id): bool => $id > 0)));
    }
}

<?php

declare(strict_types=1);

namespace Nordwerk\TeasersBundle\Backend;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Nordwerk\TeasersBundle\Source\SourceRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TeaserFields
{
    public function __construct(
        private readonly SourceRegistry $sources,
        private readonly TranslatorInterface $translator,
        private readonly Connection $connection,
    ) {
    }

    /**
     * Shows only the filters the chosen source knows: news categories need the
     * Codefog extension, stars belong to testimonials.
     */
    #[AsCallback(table: 'tl_content', target: 'config.onload')]
    #[AsCallback(table: 'tl_module', target: 'config.onload')]
    public function adjustPalette(DataContainer $dc): void
    {
        if (!$dc->id || !isset($GLOBALS['TL_DCA'][$dc->table]['palettes']['nw_teaser'])) {
            return;
        }
        $source = (string) ($this->connection->fetchOne('SELECT nwTeaserSource FROM '.$dc->table.' WHERE id = ?', [(int) $dc->id]) ?: '');
        $palette = PaletteManipulator::create();

        if ('news' !== $source || [] === $this->categories()) {
            $palette->removeField('nwTeaserCategories');
        }
        if ('testimonials' !== $source) {
            $palette->removeField('nwTeaserMinStars');
        }
        $palette->applyToPalette('nw_teaser', $dc->table);
    }

    /**
     * @return array<string, string>
     */
    #[AsCallback(table: 'tl_content', target: 'fields.nwTeaserSource.options')]
    #[AsCallback(table: 'tl_module', target: 'fields.nwTeaserSource.options')]
    public function sources(): array
    {
        return array_map(fn (string $label): string => $this->translator->trans($label), $this->sources->labels());
    }

    /**
     * @return array<int, string>
     */
    #[AsCallback(table: 'tl_content', target: 'fields.nwTeaserArchives.options')]
    #[AsCallback(table: 'tl_module', target: 'fields.nwTeaserArchives.options')]
    public function archives(DataContainer $dc): array
    {
        return $this->sources->get((string) ($dc->activeRecord->nwTeaserSource ?? ''))?->getArchives() ?? [];
    }

    /**
     * @return array<int, string>
     */
    #[AsCallback(table: 'tl_content', target: 'fields.nwTeaserCategories.options')]
    #[AsCallback(table: 'tl_module', target: 'fields.nwTeaserCategories.options')]
    public function categories(): array
    {
        return $this->connection->createSchemaManager()->tablesExist(['tl_news_category']) ? $this->connection->fetchAllKeyValue('SELECT id, title FROM tl_news_category ORDER BY title') : [];
    }
}

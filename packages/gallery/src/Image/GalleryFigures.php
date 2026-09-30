<?php

declare(strict_types=1);

namespace Nordwerk\GalleryBundle\Image;

use Contao\CoreBundle\Filesystem\FilesystemItem;
use Contao\CoreBundle\Filesystem\FilesystemUtil;
use Contao\CoreBundle\Filesystem\SortMode;
use Contao\CoreBundle\Filesystem\VirtualFilesystemInterface;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\CoreBundle\Image\Studio\Studio;

final class GalleryFigures
{
    /**
     * @param list<string> $validExtensions
     */
    public function __construct(
        private readonly VirtualFilesystemInterface $filesStorage,
        private readonly Studio $studio,
        private readonly array $validExtensions,
    ) {
    }

    /**
     * @param string|array<string>|null    $sources
     * @param int|string|array<mixed>|null $size
     *
     * @return list<Figure>
     */
    public function build(array|string|null $sources, array|int|string|null $size, string $sort = 'custom', bool $lightbox = true): array
    {
        $items = FilesystemUtil::listContentsFromSerialized($this->filesStorage, $sources)
            ->filter(fn (FilesystemItem $item): bool => \in_array($item->getExtension(true), $this->validExtensions, true))
        ;

        if ($mode = SortMode::tryFrom($sort)) {
            $items = $items->sort($mode);
        }

        $builder = $this->studio->createFigureBuilder()->setSize($size)->enableLightbox($lightbox);
        $figures = [];

        foreach ($items as $item) {
            if ($figure = $builder->fromStorage($this->filesStorage, $item->getPath())->buildIfResourceExists()) {
                $figures[] = $figure;
            }
        }

        return $figures;
    }
}

<?php

declare(strict_types=1);

namespace Nordwerk\GalleryBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Nordwerk\CarouselBundle\NordwerkCarouselBundle;
use Nordwerk\GalleryBundle\NordwerkGalleryBundle;
use Nordwerk\SheetBundle\NordwerkSheetBundle;

final class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [BundleConfig::create(NordwerkGalleryBundle::class)->setLoadAfter([ContaoCoreBundle::class, NordwerkCarouselBundle::class, NordwerkSheetBundle::class])];
    }
}

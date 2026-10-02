<?php

declare(strict_types=1);

namespace Nordwerk\SectionsBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Nordwerk\CarouselBundle\NordwerkCarouselBundle;
use Nordwerk\SectionsBundle\NordwerkSectionsBundle;

final class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [BundleConfig::create(NordwerkSectionsBundle::class)->setLoadAfter([ContaoCoreBundle::class, NordwerkCarouselBundle::class])];
    }
}

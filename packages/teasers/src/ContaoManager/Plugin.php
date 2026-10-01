<?php

declare(strict_types=1);

namespace Nordwerk\TeasersBundle\ContaoManager;

use Contao\CalendarBundle\ContaoCalendarBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\NewsBundle\ContaoNewsBundle;
use Nordwerk\CarouselBundle\NordwerkCarouselBundle;
use Nordwerk\TeasersBundle\NordwerkTeasersBundle;

final class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [BundleConfig::create(NordwerkTeasersBundle::class)->setLoadAfter([ContaoCoreBundle::class, ContaoNewsBundle::class, ContaoCalendarBundle::class, NordwerkCarouselBundle::class])];
    }
}

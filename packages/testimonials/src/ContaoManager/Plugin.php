<?php

declare(strict_types=1);

namespace Nordwerk\TestimonialsBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Nordwerk\TeasersBundle\NordwerkTeasersBundle;
use Nordwerk\TestimonialsBundle\NordwerkTestimonialsBundle;

final class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [BundleConfig::create(NordwerkTestimonialsBundle::class)->setLoadAfter([ContaoCoreBundle::class, NordwerkTeasersBundle::class])];
    }
}

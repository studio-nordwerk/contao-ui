<?php

declare(strict_types=1);

namespace Nordwerk\SheetBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Nordwerk\SheetBundle\NordwerkSheetBundle;

final class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [BundleConfig::create(NordwerkSheetBundle::class)->setLoadAfter([ContaoCoreBundle::class])];
    }
}

<?php

declare(strict_types=1);

namespace Nordwerk\ContaoUi\Tests;

use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Nordwerk\CarouselBundle\ContaoManager\Plugin as CarouselPlugin;
use Nordwerk\CarouselBundle\NordwerkCarouselBundle;
use Nordwerk\GalleryBundle\ContaoManager\Plugin as GalleryPlugin;
use Nordwerk\GalleryBundle\NordwerkGalleryBundle;
use Nordwerk\SectionsBundle\ContaoManager\Plugin as SectionsPlugin;
use Nordwerk\SectionsBundle\NordwerkSectionsBundle;
use Nordwerk\SheetBundle\ContaoManager\Plugin as SheetPlugin;
use Nordwerk\SheetBundle\NordwerkSheetBundle;
use PHPUnit\Framework\TestCase;

final class BundleRegistrationTest extends TestCase
{
    public function testManagerRegistersIndependentlyInstallableBundles(): void
    {
        $parser = $this->createMock(ParserInterface::class);

        foreach ([new CarouselPlugin(), new SheetPlugin(), new GalleryPlugin(), new SectionsPlugin()] as $plugin) {
            $this->assertCount(1, $plugin->getBundles($parser));
        }

        foreach ([new NordwerkCarouselBundle(), new NordwerkSheetBundle(), new NordwerkGalleryBundle(), new NordwerkSectionsBundle()] as $bundle) {
            $this->assertFileExists($bundle->getPath().'/composer.json');
            $this->assertFileExists($bundle->getPath().'/config/services.yaml');
        }
    }
}

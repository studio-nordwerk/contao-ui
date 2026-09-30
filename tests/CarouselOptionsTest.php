<?php

declare(strict_types=1);

namespace Nordwerk\ContaoUi\Tests;

use Nordwerk\CarouselBundle\Options\CarouselOptions;
use PHPUnit\Framework\TestCase;

final class CarouselOptionsTest extends TestCase
{
    public function testLayoutCannotInjectCssAndInvalidRangesAreClamped(): void
    {
        $options = CarouselOptions::fromData(['nwCarouselSmall' => '1; color:red', 'nwCarouselMedium' => -2, 'nwCarouselLarge' => 999, 'nwCarouselAutoplay' => 50]);
        $this->assertSame(1.0, $options['small']);
        $this->assertSame(1.0, $options['medium']);
        $this->assertSame(12.0, $options['large']);
        $this->assertSame(1000, $options['autoplay']);
    }

    public function testFractionalViewsAndDisabledEnhancementsArePreserved(): void
    {
        $options = CarouselOptions::fromData(['nwCarouselSmall' => '1.25', 'nwCarouselAutoplay' => '0', 'nwCarouselArrows' => '', 'nwCarouselDots' => '1']);
        $this->assertSame(1.25, $options['small']);
        $this->assertSame(0, $options['autoplay']);
        $this->assertFalse($options['arrows']);
        $this->assertTrue($options['dots']);
    }
}

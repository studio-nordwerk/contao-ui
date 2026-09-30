<?php

declare(strict_types=1);

namespace Nordwerk\ContaoUi\Tests;

use Nordwerk\SheetBundle\Options\SheetOptions;
use PHPUnit\Framework\TestCase;

final class SheetOptionsTest extends TestCase
{
    public function testSnapPointsAreUniqueSortedNumericViewportHeights(): void
    {
        $options = SheetOptions::fromData(['nwSheetPresentation' => 'center" onload="bad', 'nwSheetSnapPoints' => '75,50,50,0,100,50dvh; color:red', 'nwSheetDismissible' => '']);
        $this->assertSame('bottom', $options['presentation']);
        $this->assertSame([50, 75], $options['snaps']);
        $this->assertFalse($options['dismissible']);
    }
}

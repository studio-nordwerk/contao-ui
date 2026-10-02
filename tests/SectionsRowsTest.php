<?php

declare(strict_types=1);

namespace Nordwerk\ContaoUi\Tests;

use Nordwerk\SectionsBundle\Section\Icons;
use Nordwerk\SectionsBundle\Section\Rows;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SectionsRowsTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function figures(): iterable
    {
        yield 'year' => ['2019', '2019', ''];
        yield 'unit' => ['6 Wo.', '6', 'Wo.'];
        yield 'unit without space' => ['3Std.', '3', 'Std.'];
        yield 'decimal comma' => ['4,9 von 5', '4,9', 'von 5'];
        yield 'plus' => ['600+', '600+', ''];
        yield 'percent' => ['98 %', '98 %', ''];
        yield 'thousands' => ['1.200 kg', '1.200', 'kg'];
        yield 'no number' => ['viele', 'viele', ''];
    }

    #[DataProvider('figures')]
    public function testSplitsFigureIntoNumberAndUnit(string $value, string $number, string $unit): void
    {
        $this->assertSame(['number' => $number, 'unit' => $unit], Rows::figure($value));
    }

    public function testDropsEmptyAndDisabledRowsAndLimits(): void
    {
        $value = serialize([
            ['title' => ' Erster ', 'text' => 'Text'],
            ['title' => '', 'text' => ''],
            ['title' => 'Aus', 'text' => 'x', 'enable' => ''],
            ['title' => 'Zweiter'],
            ['title' => 'Dritter', 'text' => 'zu viel'],
        ]);

        $this->assertSame([['title' => 'Erster', 'text' => 'Text'], ['title' => 'Zweiter', 'text' => '']], Rows::from($value, ['title', 'text'], 2));
        $this->assertSame([], Rows::from(null, ['title'], 4));
        $this->assertSame([], Rows::from('kein Array', ['title'], 4));
    }

    public function testReadsNonEmptyLines(): void
    {
        $this->assertSame(['Eins', 'Zwei', 'Drei'], Rows::lines("Eins\r\n\n  Zwei \nDrei\nVier", 3));
    }

    public function testDecodesContaoInputEncodingOnce(): void
    {
        $this->assertSame('030 000000 (Demo)', Rows::plain(' 030 000000 &#40;Demo&#41; '));
        $this->assertSame('/seite?a=1&b=2', Rows::plain('/seite?a=1&amp;b=2'));
        $this->assertSame([['days' => 'Do – Fr', 'times' => '12 – 18 Uhr']], Rows::from(serialize([['days' => 'Do &#8211; Fr', 'times' => '12 – 18 Uhr']]), ['days', 'times'], 4));
    }

    public function testObfuscatedTextContainsOnlyEntities(): void
    {
        $encoded = Rows::obfuscate('"<img/src=x/onerror=alert(1)>"@example.test');
        $this->assertMatchesRegularExpression('/^(&#\d+;)+$/', $encoded);
        $this->assertSame('"<img/src=x/onerror=alert(1)>"@example.test', html_entity_decode($encoded, ENT_QUOTES | ENT_HTML5));
        $this->assertSame('&#252;', Rows::obfuscate('ü'));
    }

    public function testEveryOfferedIconHasAPath(): void
    {
        foreach (Icons::OFFERED as $name) {
            $this->assertArrayHasKey($name, Icons::PATHS);
        }
        $this->assertStringContainsString('aria-hidden="true"', Icons::svg('leaf'));
        $this->assertStringContainsString(Icons::PATHS['sparkle'], Icons::svg('unknown'));
    }
}

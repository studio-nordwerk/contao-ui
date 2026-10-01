<?php

declare(strict_types=1);

namespace Nordwerk\ContaoUi\Tests;

use Nordwerk\TeasersBundle\Card\Card;
use Nordwerk\TeasersBundle\Query\TeaserQuery;
use Nordwerk\TeasersBundle\Source\SourceRegistry;
use Nordwerk\TeasersBundle\Source\TeaserSourceInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TeaserContractTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string|null}>
     */
    public static function links(): iterable
    {
        yield 'reader URL' => ['/news/example.html', '/news/example.html'];
        yield 'external HTTPS' => ['https://example.test/product', 'https://example.test/product'];
        yield 'script' => ['javascript:alert(1)', null];
        yield 'data' => ['data:text/html,test', null];
        yield 'protocol relative' => ['//example.test', null];
        yield 'backslash' => ['/\\example.test', null];
        yield 'leading control' => ["\njavascript:alert(1)", null];
    }

    #[DataProvider('links')]
    public function testLinksRejectUnsafeSchemes(string $value, string|null $expected): void
    {
        $this->assertSame($expected, (new Card('Title', link: $value))->link);
    }

    public function testInvalidStarsCannotEnterTheCardModel(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Card('Title', stars: 6);
    }

    public function testRegistryRejectsAmbiguousSourceKeys(): void
    {
        $source = new class() implements TeaserSourceInterface {
            public function getKey(): string
            {
                return 'products';
            }

            public function getLabel(): string
            {
                return 'products.label';
            }

            public function getArchives(): array
            {
                return [];
            }

            public function fetch(TeaserQuery $query): iterable
            {
                return [];
            }
        };
        $this->assertSame($source, (new SourceRegistry([$source]))->get('products'));
        $this->assertNull((new SourceRegistry([$source]))->get('missing'));
        $this->expectException(\LogicException::class);
        new SourceRegistry([$source, $source]);
    }

    public function testQueryConstrainsTamperedSettings(): void
    {
        $query = TeaserQuery::fromData(['nwTeaserArchives' => serialize([2, '3', 2, -1]), 'nwTeaserLimit' => 900, 'nwTeaserSort' => 'SQL injection', 'nwTeaserMinStars' => -5]);
        $this->assertSame([2, 3], $query->archives);
        $this->assertSame(100, $query->limit);
        $this->assertSame('date_desc', $query->sort);
        $this->assertSame(0, $query->minStars);
    }
}

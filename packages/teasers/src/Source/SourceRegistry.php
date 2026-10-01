<?php

declare(strict_types=1);

namespace Nordwerk\TeasersBundle\Source;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class SourceRegistry
{
    /**
     * @var array<string, TeaserSourceInterface>
     */
    private array $sources = [];

    /**
     * @param iterable<TeaserSourceInterface> $sources
     */
    public function __construct(#[AutowireIterator('nordwerk.teaser_source')] iterable $sources)
    {
        foreach ($sources as $source) {
            $key = $source->getKey();

            if (!preg_match('/^[a-z][a-z0-9_]*$/', $key) || isset($this->sources[$key])) {
                throw new \LogicException('Invalid or duplicate teaser source key: '.$key);
            }

            $this->sources[$key] = $source;
        }
    }

    public function get(string $key): TeaserSourceInterface|null
    {
        return $this->sources[$key] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return array_map(static fn (TeaserSourceInterface $source): string => $source->getLabel(), $this->sources);
    }
}

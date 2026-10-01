<?php

declare(strict_types=1);

namespace Nordwerk\TeasersBundle\Source;

use Nordwerk\TeasersBundle\Card\Card;
use Nordwerk\TeasersBundle\Query\TeaserQuery;

interface TeaserSourceInterface
{
    /**
     * Unique, persistable ASCII key: [a-z][a-z0-9_]{0,63} (1–64 characters).
     */
    public function getKey(): string;

    /**
     * Return a translation key in the messages domain.
     */
    public function getLabel(): string;

    /**
     * Only choices authorized for the current backend user.
     *
     * @return array<int, string>
     */
    public function getArchives(): array;

    /**
     * Return public, authorized entries in the requested order, up to the limit. Text
     * and meta are plain text. Image is a local Contao file UUID or path.
     *
     * @return iterable<Card>
     */
    public function fetch(TeaserQuery $query): iterable;
}

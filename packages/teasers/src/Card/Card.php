<?php

declare(strict_types=1);

namespace Nordwerk\TeasersBundle\Card;

final readonly class Card
{
    public string|null $link;

    /**
     * @param array<string, string> $meta
     */
    public function __construct(
        public string $title,
        public string $text = '',
        public string|null $image = null,
        string|null $link = null,
        public \DateTimeImmutable|null $date = null,
        public array $meta = [],
        public string|null $price = null,
        public int|null $stars = null,
    ) {
        $this->link = self::safeLink($link);

        if (null !== $stars && ($stars < 1 || $stars > 5)) {
            throw new \InvalidArgumentException('Stars must be between 1 and 5.');
        }
    }

    private static function safeLink(string|null $link): string|null
    {
        if (null === $link || '' === $link || preg_match('/[\x00-\x20\\\\]/', $link)) {
            return null;
        }

        if (preg_match('~^https?://~i', $link)) {
            return false !== filter_var($link, FILTER_VALIDATE_URL) ? $link : null;
        }

        return preg_match('~^(?:/(?!/)|\#)~', $link) ? $link : null;
    }
}

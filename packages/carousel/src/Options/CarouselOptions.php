<?php

declare(strict_types=1);

namespace Nordwerk\CarouselBundle\Options;

final class CarouselOptions
{
    /**
     * @param array<string, mixed> $data
     *
     * @return array{small: float, medium: float, large: float, arrows: bool, dots: bool, drag: bool, autoplay: int}
     */
    public static function fromData(array $data): array
    {
        $number = static fn (mixed $value): float => is_numeric($value) ? max(1.0, min(12.0, (float) $value)) : 1.0;
        $delay = $data['nwCarouselAutoplay'] ?? 0;

        return [
            'small' => $number($data['nwCarouselSmall'] ?? 1),
            'medium' => $number($data['nwCarouselMedium'] ?? 1),
            'large' => $number($data['nwCarouselLarge'] ?? 1),
            'arrows' => (bool) ($data['nwCarouselArrows'] ?? true),
            'dots' => (bool) ($data['nwCarouselDots'] ?? true),
            'drag' => (bool) ($data['nwCarouselDrag'] ?? false),
            'autoplay' => is_numeric($delay) && (int) $delay > 0 ? max(1000, min(60000, (int) $delay)) : 0,
        ];
    }
}

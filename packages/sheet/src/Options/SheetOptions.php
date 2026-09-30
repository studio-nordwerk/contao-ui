<?php

declare(strict_types=1);

namespace Nordwerk\SheetBundle\Options;

final class SheetOptions
{
    /**
     * @param array<string, mixed> $data
     *
     * @return array{presentation: string, snaps: list<int>, dismissible: bool, drag: bool, history: bool}
     */
    public static function fromData(array $data): array
    {
        $presentation = $data['nwSheetPresentation'] ?? 'bottom';
        $snaps = [];
        $input = $data['nwSheetSnapPoints'] ?? '';

        foreach (explode(',', \is_string($input) ? $input : '') as $point) {
            $point = trim($point);
            if (ctype_digit($point) && (int) $point >= 5 && (int) $point <= 95) {
                $snaps[] = (int) $point;
            }
        }

        $snaps = array_values(array_unique($snaps));
        sort($snaps, SORT_NUMERIC);

        return [
            'presentation' => \in_array($presentation, ['bottom', 'start', 'end', 'center'], true) ? $presentation : 'bottom',
            'snaps' => $snaps,
            'dismissible' => (bool) ($data['nwSheetDismissible'] ?? true),
            'drag' => (bool) ($data['nwSheetDrag'] ?? false),
            'history' => (bool) ($data['nwSheetHistory'] ?? false),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Nordwerk\SectionsBundle\Section;

/**
 * Line icons for the features section: 24 × 24, drawn with the current text color.
 */
final class Icons
{
    public const PATHS = [
        'leaf' => '<path d="M5 19c9 0 14-6 14-14-8 0-14 5-14 14Z"/><path d="m5 19 8-8"/>',
        'drop' => '<path d="M12 3.5s6 6.3 6 10.5a6 6 0 0 1-12 0c0-4.2 6-10.5 6-10.5Z"/><path d="M9.5 14.5a2.6 2.6 0 0 0 2.5 2.5"/>',
        'box' => '<path d="M4 7.5 12 4l8 3.5v9L12 20l-8-3.5Z"/><path d="m4 7.5 8 3.5 8-3.5M12 11v9"/>',
        'hourglass' => '<path d="M7 3.5h10M7 20.5h10M8 3.5v2.2c0 2.2 4 4 4 6.3 0-2.3 4-4.1 4-6.3V3.5M8 20.5v-2.2c0-2.2 4-4 4-6.3 0 2.3 4 4.1 4 6.3v2.2"/>',
        'people' => '<circle cx="9" cy="8" r="3.2"/><path d="M3.5 19c.6-3.2 2.8-5 5.5-5s4.9 1.8 5.5 5"/><circle cx="17" cy="9" r="2.5"/><path d="M15.6 14.1c2.6-.3 4.4 1.4 4.9 4.4"/>',
        'checklist' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1M9 11.5l2 2 4-4M9 17h6"/>',
        'gift' => '<rect x="4" y="9" width="16" height="11" rx="1.5"/><path d="M3 9h18M12 9v11M12 9c-2.5 0-4.5-1.2-4.5-3a2.2 2.2 0 0 1 4.5 0M12 9c2.5 0 4.5-1.2 4.5-3a2.2 2.2 0 0 0-4.5 0"/>',
        'door' => '<path d="M6 20.5V4h10v16.5M3.5 20.5h17"/><path d="M13 12.5h.01"/>',
        'heart' => '<path d="M12 19.5s-7.5-4.4-7.5-10A4.2 4.2 0 0 1 12 7a4.2 4.2 0 0 1 7.5 2.5c0 5.6-7.5 10-7.5 10Z"/>',
        'truck' => '<path d="M3 6.5h11v10H3zM14 10h3.8l3.2 3.4v3.1h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/>',
        'clock' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
        'calendar' => '<rect x="4" y="5.5" width="16" height="14.5" rx="2"/><path d="M4 10h16M8.5 3.5v4M15.5 3.5v4"/>',
        'shield' => '<path d="M12 3.5 19 6v5.5c0 4.3-3 7.6-7 9-4-1.4-7-4.7-7-9V6Z"/><path d="m9 12 2 2 4-4"/>',
        'chat' => '<path d="M4.5 6.5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H10l-4 3.5v-3.5h0a1.5 1.5 0 0 1-1.5-1.5Z"/>',
        'hand' => '<path d="M8 12.5V6a1.5 1.5 0 0 1 3 0v5M11 11V4.5a1.5 1.5 0 0 1 3 0V11M14 11V6a1.5 1.5 0 0 1 3 0v7.5c0 3.6-2.4 6.5-6 6.5-2.5 0-3.9-1.2-5.3-3.3L4 13.6a1.5 1.5 0 0 1 2.5-1.6L8 14"/>',
        'sparkle' => '<path d="M12 3.5c.8 4.4 2.6 6.3 7 7-4.4.8-6.2 2.6-7 7-.8-4.4-2.6-6.2-7-7 4.4-.7 6.2-2.6 7-7Z"/>',
        'pin' => '<path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11Z"/><circle cx="12" cy="10" r="2.3"/>',
        'phone' => '<path d="M5 4h3.5l1.5 4-2 1.3a10 10 0 0 0 4.7 4.7L14 12l4 1.5V17a2 2 0 0 1-2 2A13 13 0 0 1 3 6a2 2 0 0 1 2-2Z"/>',
        'mail' => '<rect x="3.5" y="5.5" width="17" height="13" rx="2"/><path d="m4 7 8 6 8-6"/>',
    ];

    /**
     * Icons offered in the features section; pin, phone and mail belong to the
     * contact section.
     */
    public const OFFERED = ['leaf', 'drop', 'box', 'hourglass', 'people', 'checklist', 'gift', 'door', 'heart', 'truck', 'clock', 'calendar', 'shield', 'chat', 'hand', 'sparkle'];

    public static function svg(string $name, string $class = 'nw-icon'): string
    {
        $path = self::PATHS[$name] ?? self::PATHS['sparkle'];

        return '<svg class="'.$class.'" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'.$path.'</svg>';
    }
}

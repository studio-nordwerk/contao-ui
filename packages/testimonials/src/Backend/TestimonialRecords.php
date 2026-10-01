<?php

declare(strict_types=1);

namespace Nordwerk\TestimonialsBundle\Backend;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\StringUtil;

final class TestimonialRecords
{
    /**
     * @param array<string, mixed> $row
     */
    #[AsCallback(table: 'tl_nw_testimonial', target: 'list.sorting.child_record')]
    public function label(array $row): string
    {
        return '<div>'.StringUtil::specialchars($row['name']).' — '.StringUtil::specialchars(mb_substr((string) $row['text'], 0, 100)).'</div>';
    }

    #[AsCallback(table: 'tl_nw_testimonial', target: 'fields.published.save')]
    public function publish(mixed $value, DataContainer $dc): mixed
    {
        if ($value && '' === trim((string) ($dc->activeRecord->reviewNotes ?? ''))) {
            throw new \InvalidArgumentException($GLOBALS['TL_LANG']['tl_nw_testimonial']['reviewRequired']);
        }

        return $value;
    }
}

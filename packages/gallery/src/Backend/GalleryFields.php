<?php

declare(strict_types=1);

namespace Nordwerk\GalleryBundle\Backend;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;

final class GalleryFields
{
    #[AsCallback(table: 'tl_content', target: 'fields.multiSRC.load')]
    public function configure(mixed $value, DataContainer $dc): mixed
    {
        if ('nw_gallery' === ($dc->activeRecord->type ?? null)) {
            $GLOBALS['TL_DCA']['tl_content']['fields']['multiSRC']['eval']['isGallery'] = true;
            $GLOBALS['TL_DCA']['tl_content']['fields']['multiSRC']['eval']['extensions'] = '%contao.image.valid_extensions%';
            $GLOBALS['TL_DCA']['tl_content']['fields']['multiSRC']['eval']['mandatory'] = true;
        }

        return $value;
    }
}

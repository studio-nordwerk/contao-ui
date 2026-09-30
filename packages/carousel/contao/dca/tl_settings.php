<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{nw_carousel_legend},nwCarouselReplaceSwiper';
$GLOBALS['TL_DCA']['tl_settings']['fields']['nwCarouselReplaceSwiper'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['nwCarouselReplaceSwiper'],
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
];

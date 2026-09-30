<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_content']['palettes']['nw_carousel'] = '{type_legend},type,headline,title;{nw_carousel_legend},nwCarouselLabel,nwCarouselSmall,nwCarouselMedium,nwCarouselLarge,nwCarouselArrows,nwCarouselDots,nwCarouselDrag,nwCarouselAutoplay;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['fields']['nwCarouselLabel'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['nwCarouselLabel'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'long'],
    'sql' => "varchar(255) NOT NULL default ''",
];

foreach (['nwCarouselSmall', 'nwCarouselMedium', 'nwCarouselLarge'] as $field) {
    $GLOBALS['TL_DCA']['tl_content']['fields'][$field] = [
        'label' => &$GLOBALS['TL_LANG']['tl_content'][$field],
        'exclude' => true,
        'inputType' => 'text',
        'default' => '1',
        'eval' => ['mandatory' => true, 'rgxp' => 'digit', 'minval' => 1, 'maxval' => 12, 'tl_class' => 'w50'],
        'sql' => "decimal(4,2) NOT NULL default '1.00'",
    ];
}

foreach (['nwCarouselArrows', 'nwCarouselDots', 'nwCarouselDrag'] as $field) {
    $GLOBALS['TL_DCA']['tl_content']['fields'][$field] = [
        'label' => &$GLOBALS['TL_LANG']['tl_content'][$field],
        'exclude' => true,
        'inputType' => 'checkbox',
        'default' => 'nwCarouselDrag' !== $field,
        'eval' => ['tl_class' => 'w50'],
        'sql' => ['type' => 'boolean', 'default' => 'nwCarouselDrag' !== $field],
    ];
}

$GLOBALS['TL_DCA']['tl_content']['fields']['nwCarouselAutoplay'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['nwCarouselAutoplay'],
    'exclude' => true,
    'inputType' => 'text',
    'default' => '0',
    'eval' => ['rgxp' => 'natural', 'maxval' => 60000, 'tl_class' => 'w50'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];

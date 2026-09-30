<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_content']['palettes']['nw_gallery'] = '{type_legend},type,headline,title;{source_legend},multiSRC,sortBy;{nw_gallery_legend},nwGalleryLabel,nwGalleryLayout,perRow,size,fullsize;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['fields']['nwGalleryLabel'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['nwGalleryLabel'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'long'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['nwGalleryLayout'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['nwGalleryLayout'],
    'exclude' => true,
    'inputType' => 'select',
    'default' => 'grid',
    'options' => ['grid', 'mosaic', 'rail'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['nwGalleryOptions'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(16) NOT NULL default 'grid'",
];

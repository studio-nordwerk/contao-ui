<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_module']['palettes']['nw_teaser'] = '{title_legend},name,headline,type;{nw_teaser_legend},nwTeaserSource,nwTeaserArchives,nwTeaserCategories,nwTeaserMinStars,nwTeaserSort,nwTeaserLimit,nwTeaserLayout,nwTeaserLabel,nwTeaserColumns;{image_legend},imgSize;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID;';

foreach ([
    'nwTeaserSource' => ['select', "varchar(64) NOT NULL default ''", ['submitOnChange' => true, 'mandatory' => true]],
    'nwTeaserArchives' => ['checkbox', 'blob NULL', ['multiple' => true, 'mandatory' => true]],
    'nwTeaserCategories' => ['checkbox', 'blob NULL', ['multiple' => true]],
    'nwTeaserMinStars' => ['text', "int(10) unsigned NOT NULL default '0'", ['rgxp' => 'digit', 'minval' => 0, 'maxval' => 5]],
    'nwTeaserSort' => ['select', "varchar(32) NOT NULL default 'date_desc'", []],
    'nwTeaserLimit' => ['text', "int(10) unsigned NOT NULL default '6'", ['rgxp' => 'digit', 'mandatory' => true, 'minval' => 1, 'maxval' => 100]],
    'nwTeaserLayout' => ['select', "varchar(16) NOT NULL default 'grid'", []],
    'nwTeaserLabel' => ['text', "varchar(255) NOT NULL default ''", ['maxlength' => 255, 'mandatory' => true]],
    'nwTeaserColumns' => ['text', "int(10) unsigned NOT NULL default '3'", ['rgxp' => 'digit', 'mandatory' => true, 'minval' => 1, 'maxval' => 6]],
] as $field => [$input, $sql, $eval]) {
    $GLOBALS['TL_DCA']['tl_module']['fields'][$field] = [
        'label' => &$GLOBALS['TL_LANG']['tl_module'][$field],
        'exclude' => true,
        'inputType' => $input,
        'eval' => $eval + ['tl_class' => 'clr'],
        'sql' => $sql,
    ];
}

$GLOBALS['TL_DCA']['tl_module']['fields']['nwTeaserLimit']['default'] = 6;
$GLOBALS['TL_DCA']['tl_module']['fields']['nwTeaserColumns']['default'] = 3;
$GLOBALS['TL_DCA']['tl_module']['fields']['nwTeaserSort']['options'] = ['date_desc', 'date_asc', 'title_asc', 'stars_desc'];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwTeaserSort']['reference'] = &$GLOBALS['TL_LANG']['tl_module']['nwTeaserSortOptions'];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwTeaserLayout']['options'] = ['grid', 'list', 'carousel'];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwTeaserLayout']['reference'] = &$GLOBALS['TL_LANG']['tl_module']['nwTeaserLayoutOptions'];

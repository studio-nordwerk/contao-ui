<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_content']['palettes']['nw_teaser'] = '{type_legend},type,headline;{nw_teaser_legend},nwTeaserSource,nwTeaserArchives,nwTeaserCategories,nwTeaserMinStars,nwTeaserSort,nwTeaserLimit,nwTeaserLayout,nwTeaserColumns,nwTeaserLabel;{image_legend},size;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';

foreach ([
    'nwTeaserSource' => ['select', "varchar(64) NOT NULL default ''", ['submitOnChange' => true, 'mandatory' => true, 'tl_class' => 'w50 clr']],
    'nwTeaserArchives' => ['checkbox', 'blob NULL', ['multiple' => true, 'mandatory' => true]],
    'nwTeaserCategories' => ['checkbox', 'blob NULL', ['multiple' => true]],
    'nwTeaserMinStars' => ['text', "int(10) unsigned NOT NULL default '0'", ['rgxp' => 'digit', 'minval' => 0, 'maxval' => 5, 'tl_class' => 'w50']],
    'nwTeaserSort' => ['select', "varchar(32) NOT NULL default 'date_desc'", ['tl_class' => 'w50 clr']],
    'nwTeaserLimit' => ['text', "int(10) unsigned NOT NULL default '6'", ['rgxp' => 'digit', 'mandatory' => true, 'minval' => 1, 'maxval' => 100, 'tl_class' => 'w50']],
    'nwTeaserLayout' => ['select', "varchar(16) NOT NULL default 'grid'", ['tl_class' => 'w50 clr']],
    'nwTeaserLabel' => ['text', "varchar(255) NOT NULL default ''", ['maxlength' => 255, 'tl_class' => 'clr long']],
    'nwTeaserColumns' => ['text', "int(10) unsigned NOT NULL default '3'", ['rgxp' => 'digit', 'mandatory' => true, 'minval' => 1, 'maxval' => 6, 'tl_class' => 'w50']],
] as $field => [$input, $sql, $eval]) {
    $GLOBALS['TL_DCA']['tl_content']['fields'][$field] = [
        'label' => &$GLOBALS['TL_LANG']['tl_content'][$field],
        'exclude' => true,
        'inputType' => $input,
        'eval' => $eval + ['tl_class' => 'clr'],
        'sql' => $sql,
    ];
}

$GLOBALS['TL_DCA']['tl_content']['fields']['nwTeaserLimit']['default'] = 6;
$GLOBALS['TL_DCA']['tl_content']['fields']['nwTeaserColumns']['default'] = 3;
$GLOBALS['TL_DCA']['tl_content']['fields']['nwTeaserSort']['options'] = ['date_desc', 'date_asc', 'title_asc', 'stars_desc'];
$GLOBALS['TL_DCA']['tl_content']['fields']['nwTeaserSort']['reference'] = &$GLOBALS['TL_LANG']['tl_content']['nwTeaserSortOptions'];
$GLOBALS['TL_DCA']['tl_content']['fields']['nwTeaserLayout']['options'] = ['grid', 'list', 'carousel'];
$GLOBALS['TL_DCA']['tl_content']['fields']['nwTeaserLayout']['reference'] = &$GLOBALS['TL_LANG']['tl_content']['nwTeaserLayoutOptions'];

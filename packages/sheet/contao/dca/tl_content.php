<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_content']['palettes']['nw_sheet'] = '{type_legend},type,title;{nw_sheet_legend},nwSheetLabel,nwSheetPresentation,nwSheetSnapPoints,nwSheetDismissible,nwSheetDrag,nwSheetHistory;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['palettes']['nw_sheet_button'] = '{type_legend},type,title;{nw_sheet_legend},nwSheetButtonLabel,nwSheetTarget,nwSheetAction;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['fields']['nwSheetLabel'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['nwSheetLabel'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'long'],
    'sql' => 'varchar(255) NOT NULL default \'\'',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['nwSheetPresentation'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['nwSheetPresentation'],
    'exclude' => true,
    'inputType' => 'select',
    'default' => 'bottom',
    'options' => ['bottom', 'start', 'end', 'center'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['nwSheetOptions'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => 'varchar(16) NOT NULL default \'bottom\'',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['nwSheetSnapPoints'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['nwSheetSnapPoints'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['maxlength' => 128, 'tl_class' => 'w50'],
    'sql' => 'varchar(128) NOT NULL default \'\'',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['nwSheetDismissible'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['nwSheetDismissible'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'default' => true,
    'eval' => ['tl_class' => 'w50'],
    'sql' => ['type' => 'boolean', 'default' => true],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['nwSheetDrag'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['nwSheetDrag'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['nwSheetHistory'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['nwSheetHistory'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['nwSheetTarget'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['nwSheetTarget'],
    'exclude' => true,
    'inputType' => 'select',
    'eval' => ['mandatory' => true, 'chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
    'sql' => 'int(10) unsigned NOT NULL default \'0\'',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['nwSheetAction'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['nwSheetAction'],
    'exclude' => true,
    'inputType' => 'select',
    'default' => 'show-modal',
    'options' => ['show-modal', 'close'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['nwSheetOptions'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => 'varchar(16) NOT NULL default \'show-modal\'',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['nwSheetButtonLabel'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['nwSheetButtonLabel'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'long'],
    'sql' => 'varchar(255) NOT NULL default \'\'',
];

<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_module']['palettes']['nw_offcanvas_navigation'] = '{title_legend},name,type;{nw_sheet_legend},nwSheetNavigation,nwSheetPresentation;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID';

$GLOBALS['TL_DCA']['tl_module']['fields']['nwSheetNavigation'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['nwSheetNavigation'],
    'exclude' => true,
    'inputType' => 'select',
    'eval' => ['mandatory' => true, 'chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
    'sql' => 'int(10) unsigned NOT NULL default \'0\'',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['nwSheetPresentation'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['nwSheetPresentation'],
    'exclude' => true,
    'inputType' => 'select',
    'default' => 'end',
    'options' => ['bottom', 'start', 'end', 'center'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['nwSheetOptions'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => 'varchar(16) NOT NULL default \'end\'',
];

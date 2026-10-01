<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_nw_testimonial_archive'] = [
    'config' => ['dataContainer' => DC_Table::class, 'ctable' => ['tl_nw_testimonial'], 'enableVersioning' => true, 'sql' => ['keys' => ['id' => 'primary']]],
    'list' => [
        'sorting' => ['mode' => DataContainer::MODE_SORTED, 'fields' => ['title'], 'flag' => DataContainer::SORT_INITIAL_LETTER_ASC, 'panelLayout' => 'search,limit'],
        'label' => ['fields' => ['title'], 'format' => '%s'],
        'global_operations' => ['all'],
        'operations' => ['edit' => ['href' => 'table=tl_nw_testimonial', 'icon' => 'edit.svg'], 'editheader', 'delete', 'show'],
    ],
    'palettes' => ['default' => '{title_legend},title,verification'],
    'fields' => [
        'id' => ['sql' => 'int(10) unsigned NOT NULL auto_increment'],
        'tstamp' => ['sql' => "int(10) unsigned NOT NULL default '0'"],
        'title' => ['inputType' => 'text', 'search' => true, 'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'long'], 'sql' => "varchar(255) NOT NULL default ''"],
        'verification' => ['inputType' => 'textarea', 'eval' => ['mandatory' => true, 'maxlength' => 2000, 'tl_class' => 'clr'], 'sql' => 'text NULL'],
    ],
];

<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_nw_testimonial'] = [
    'config' => ['dataContainer' => DC_Table::class, 'ptable' => 'tl_nw_testimonial_archive', 'enableVersioning' => true, 'sql' => ['keys' => ['id' => 'primary', 'pid,published,date' => 'index', 'importKey' => 'unique']]],
    'list' => [
        'sorting' => ['mode' => DataContainer::MODE_PARENT, 'fields' => ['date DESC'], 'headerFields' => ['title', 'verification'], 'panelLayout' => 'filter;search,limit'],
        'label' => ['fields' => ['name', 'published'], 'format' => '%s [%s]'],
        'global_operations' => ['all'],
        'operations' => ['edit', 'copy', 'delete', 'toggle', 'show'],
    ],
    'palettes' => ['default' => '{title_legend},name,role,text,stars,date,singleSRC,source;{review_legend},email,consentedAt,consentText,provenance,reviewNotes;{publish_legend},published'],
    'fields' => [
        'id' => ['sql' => 'int(10) unsigned NOT NULL auto_increment'],
        'pid' => ['foreignKey' => 'tl_nw_testimonial_archive.title', 'sql' => "int(10) unsigned NOT NULL default '0'", 'relation' => ['type' => 'belongsTo', 'load' => 'lazy']],
        'tstamp' => ['sql' => "int(10) unsigned NOT NULL default '0'"],
        'name' => ['inputType' => 'text', 'search' => true, 'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'role' => ['inputType' => 'text', 'search' => true, 'eval' => ['maxlength' => 255, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'text' => ['inputType' => 'textarea', 'search' => true, 'eval' => ['mandatory' => true, 'maxlength' => 10000, 'tl_class' => 'clr'], 'sql' => 'text NULL'],
        'stars' => ['inputType' => 'select', 'options' => [1, 2, 3, 4, 5], 'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'], 'sql' => "int(10) unsigned NOT NULL default '0'"],
        'date' => ['default' => time(), 'inputType' => 'text', 'eval' => ['rgxp' => 'datim', 'mandatory' => true, 'datepicker' => true, 'tl_class' => 'w50 wizard'], 'sql' => "int(10) unsigned NOT NULL default '0'"],
        'singleSRC' => ['inputType' => 'fileTree', 'eval' => ['filesOnly' => true, 'fieldType' => 'radio', 'extensions' => '%contao.image.valid_extensions%', 'tl_class' => 'clr'], 'sql' => 'binary(16) NULL'],
        'source' => ['inputType' => 'text', 'eval' => ['maxlength' => 255, 'tl_class' => 'long'], 'sql' => "varchar(255) NOT NULL default ''"],
        'email' => ['inputType' => 'text', 'eval' => ['maxlength' => 255, 'rgxp' => 'email', 'tl_class' => 'long'], 'sql' => "varchar(255) NOT NULL default ''"],
        'consentedAt' => ['inputType' => 'text', 'eval' => ['readonly' => true, 'rgxp' => 'datim', 'tl_class' => 'long'], 'sql' => "int(10) unsigned NOT NULL default '0'"],
        'consentText' => ['inputType' => 'textarea', 'eval' => ['readonly' => true, 'tl_class' => 'clr'], 'sql' => 'text NULL'],
        'provenance' => ['inputType' => 'textarea', 'eval' => ['readonly' => true, 'tl_class' => 'clr'], 'sql' => 'mediumtext NULL'],
        'reviewNotes' => ['inputType' => 'textarea', 'eval' => ['tl_class' => 'clr'], 'sql' => 'text NULL'],
        'published' => ['inputType' => 'checkbox', 'toggle' => true, 'filter' => true, 'eval' => ['doNotCopy' => true], 'sql' => "char(1) NOT NULL default ''"],
        'importKey' => ['eval' => ['doNotCopy' => true], 'sql' => 'varchar(128) NULL'],
        'notifyRecipient' => ['sql' => "varchar(255) NOT NULL default ''"],
        'notifiedAt' => ['sql' => "int(10) unsigned NOT NULL default '0'"],
    ],
];

foreach ($GLOBALS['TL_DCA']['tl_nw_testimonial']['fields'] as $field => &$config) {
    $config['label'] = &$GLOBALS['TL_LANG']['tl_nw_testimonial'][$field];
    $config['exclude'] = true;
}
unset($config);

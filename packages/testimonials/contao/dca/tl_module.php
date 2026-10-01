<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_module']['palettes']['nw_testimonial_form'] = '{title_legend},name,headline,type;{nw_testimonial_legend},nwTestimonialArchive,nwTestimonialRecipient,nwTestimonialConsent,nwTestimonialPrivacy;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID';

$GLOBALS['TL_DCA']['tl_module']['fields']['nwTestimonialArchive'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['nwTestimonialArchive'],
    'exclude' => true,
    'inputType' => 'select',
    'foreignKey' => 'tl_nw_testimonial_archive.title',
    'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwTestimonialRecipient'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['nwTestimonialRecipient'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'rgxp' => 'email', 'maxlength' => 255, 'tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwTestimonialConsent'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['nwTestimonialConsent'],
    'exclude' => true,
    'inputType' => 'textarea',
    'eval' => ['mandatory' => true, 'maxlength' => 4000, 'tl_class' => 'clr'],
    'sql' => 'text NULL',
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwTestimonialPrivacy'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['nwTestimonialPrivacy'],
    'exclude' => true,
    'inputType' => 'pageTree',
    'eval' => ['mandatory' => true, 'fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];

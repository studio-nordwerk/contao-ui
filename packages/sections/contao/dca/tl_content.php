<?php

declare(strict_types=1);

use Nordwerk\SectionsBundle\Section\Icons;

/*
 * Page sections. Each element has a few plain fields; none needs a CSS class.
 * Pictures and links are optional here, so they keep their own fields: the core
 * fields singleSRC and url are mandatory. The picture size is the core field.
 * Labels and option names live in translations/contao_tl_content.*.yaml.
 */
$dca = &$GLOBALS['TL_DCA']['tl_content'];
$tail = '{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';
$link = '{link_legend},nwUrl,nwLinkText';

$dca['palettes']['nw_hero'] = '{type_legend},type,nwEyebrow,headline;{text_legend},text;{image_legend},nwImage,size;'.$link.',nwSecondUrl,nwSecondLinkText;'.$tail;
$dca['palettes']['nw_page_head'] = '{type_legend},type,nwEyebrow,headline;{text_legend},nwText;{image_legend},nwImage,size;'.$tail;
$dca['palettes']['nw_promises'] = '{type_legend},type;{text_legend},nwLines;'.$tail;
$dca['palettes']['nw_split'] = '{type_legend},type,headline;{text_legend},text;{image_legend},nwImage,nwImagePosition,size;'.$link.';'.$tail;
$dca['palettes']['nw_figures'] = '{type_legend},type,headline;{nw_rows_legend},nwFigures,nwNote;'.$tail;
$dca['palettes']['nw_features'] = '{type_legend},type,headline,nwIntro;{nw_rows_legend},nwFeatures;'.$tail;
$dca['palettes']['nw_steps'] = '{type_legend},type,headline,nwIntro;{nw_rows_legend},nwSteps;'.$tail;
$dca['palettes']['nw_team'] = '{type_legend},type,headline,nwIntro;{nw_team_legend},nwTeamLayout,nwNote;'.$tail;
$dca['palettes']['nw_person'] = '{type_legend},type,nwName,nwRole;{text_legend},nwText;{image_legend},nwImage,size;'.$tail;
$dca['palettes']['nw_logos'] = '{type_legend},type,headline;{image_legend},nwLogos,size;'.$tail;
$dca['palettes']['nw_contact'] = '{type_legend},type,headline;{nw_address_legend},nwName,nwStreet,nwPostal,nwCity,nwPhone,nwEmail;{nw_hours_legend},nwHours,nwHint;{nw_route_legend:hide},nwRouteUrl;'.$tail;
$dca['palettes']['nw_callout'] = '{type_legend},type,headline;{text_legend},nwText;'.$link.';'.$tail;

$label = static fn (string $field): array => ['label' => &$GLOBALS['TL_LANG']['tl_content'][$field], 'exclude' => true];
$text = static fn (string $field, int $max = 255, string $class = 'w50', array $eval = []): array => [
    ...$label($field),
    'inputType' => 'text',
    'eval' => ['maxlength' => $max, 'tl_class' => $class, ...$eval],
    'sql' => "varchar($max) NOT NULL default ''",
];
$url = static fn (string $field): array => [
    ...$label($field),
    'inputType' => 'text',
    'eval' => ['rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 2048, 'dcaPicker' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(2048) NOT NULL default ''",
];
$area = static fn (string $field, int $max = 1000, bool $mandatory = false): array => [
    ...$label($field),
    'inputType' => 'textarea',
    'eval' => ['maxlength' => $max, 'mandatory' => $mandatory, 'tl_class' => 'clr'],
    'sql' => 'text NULL',
];
$column = static fn (string $key, string $type = 'text', array $eval = []): array => ['label' => &$GLOBALS['TL_LANG']['tl_content']['nwColumns'][$key], 'inputType' => $type, 'eval' => $eval];
$rows = static fn (string $field, array $columns, array $eval): array => [
    ...$label($field),
    'inputType' => 'rowWizard',
    'fields' => $columns,
    'eval' => ['tl_class' => 'clr', ...$eval],
    'sql' => 'blob NULL',
];
$select = static fn (string $field, array $options, string $default): array => [
    ...$label($field),
    'inputType' => 'select',
    'options' => $options,
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['nwOptions'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(16) NOT NULL default '$default'",
];

$dca['fields']['nwEyebrow'] = $text('nwEyebrow', 120, 'w50 clr');
$dca['fields']['nwText'] = $area('nwText');
$dca['fields']['nwIntro'] = $area('nwIntro');
$dca['fields']['nwNote'] = $text('nwNote', 255, 'clr long');
$dca['fields']['nwUrl'] = $url('nwUrl');
$dca['fields']['nwLinkText'] = $text('nwLinkText', 80);
$dca['fields']['nwSecondUrl'] = $url('nwSecondUrl');
$dca['fields']['nwSecondLinkText'] = $text('nwSecondLinkText', 80);
$dca['fields']['nwImage'] = [
    ...$label('nwImage'),
    'inputType' => 'fileTree',
    'eval' => ['filesOnly' => true, 'fieldType' => 'radio', 'extensions' => '%contao.image.valid_extensions%', 'tl_class' => 'clr'],
    'sql' => 'binary(16) NULL',
];
$dca['fields']['nwImagePosition'] = $select('nwImagePosition', ['left', 'right'], 'left');
$dca['fields']['nwLines'] = $area('nwLines', 400, true);
$dca['fields']['nwFigures'] = $rows('nwFigures', ['value' => $column('value', 'text', ['maxlength' => 16]), 'label' => $column('label', 'text', ['maxlength' => 80])], ['min' => 2, 'max' => 4]);
$dca['fields']['nwFeatures'] = $rows('nwFeatures', [
    'icon' => ['label' => &$GLOBALS['TL_LANG']['tl_content']['nwColumns']['icon'], 'inputType' => 'select', 'options' => Icons::OFFERED, 'reference' => &$GLOBALS['TL_LANG']['tl_content']['nwIcons']],
    'title' => $column('title', 'text', ['maxlength' => 60]),
    'text' => $column('text', 'textarea', ['maxlength' => 200, 'rows' => 2]),
], ['min' => 2, 'max' => 8]);
$dca['fields']['nwSteps'] = $rows('nwSteps', ['title' => $column('title', 'text', ['maxlength' => 60]), 'text' => $column('text', 'textarea', ['maxlength' => 200, 'rows' => 2])], ['min' => 2, 'max' => 6]);
$dca['fields']['nwTeamLayout'] = $select('nwTeamLayout', ['grid', 'carousel'], 'grid');
$dca['fields']['nwName'] = $text('nwName', 120);
$dca['fields']['nwRole'] = $text('nwRole', 120);
$dca['fields']['nwLogos'] = [
    ...$label('nwLogos'),
    'inputType' => 'fileTree',
    'eval' => ['multiple' => true, 'fieldType' => 'checkbox', 'filesOnly' => true, 'isSortable' => true, 'isGallery' => true, 'extensions' => '%contao.image.valid_extensions%', 'mandatory' => true, 'tl_class' => 'clr'],
    'sql' => 'blob NULL',
];
$dca['fields']['nwStreet'] = $text('nwStreet', 120);
$dca['fields']['nwPostal'] = $text('nwPostal', 10);
$dca['fields']['nwCity'] = $text('nwCity', 80);
$dca['fields']['nwPhone'] = $text('nwPhone', 40);
$dca['fields']['nwEmail'] = $text('nwEmail', 120, 'w50', ['rgxp' => 'email', 'decodeEntities' => true]);
$dca['fields']['nwHours'] = $rows('nwHours', ['days' => $column('days', 'text', ['maxlength' => 40]), 'times' => $column('times', 'text', ['maxlength' => 60])], ['max' => 10]);
$dca['fields']['nwHint'] = $area('nwHint');
$dca['fields']['nwRouteUrl'] = $url('nwRouteUrl');

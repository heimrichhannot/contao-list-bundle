<?php

$dc = &$GLOBALS['TL_DCA']['tl_content'];

$dc['config']['onload_callback'][] = ['huh.list.backend.content', 'onLoad'];

/**
 * Palettes
 */
$dc['palettes']['__selector__'][] = 'list';
$dc['palettes']['list_preselect'] = '{type_legend},type,headline;{huh.list_legend},listConfig;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests;{invisible_legend:hide},invisible,start,stop';

/**
 * Fields
 */
$fields = [
    'listConfig'    => [
        'label'      => &$GLOBALS['TL_LANG']['tl_content']['listConfig'],
        'exclude'    => true,
        'inputType'  => 'select',
        'foreignKey' => 'tl_list_config.title',
        'relation'   => ['type' => 'belongsTo', 'load' => 'lazy'],
        'eval'       => ['tl_class' => 'w50 clr', 'mandatory' => true, 'submitOnChange' => true, 'includeBlankOption' => true],
        'sql'        => "int(10) NOT NULL default '0'",
    ],
    'listPreselect' => [
        'label'            => &$GLOBALS['TL_LANG']['tl_content']['listPreselect'],
        'exclude'          => true,
        'inputType'        => 'checkboxWizard',
        'options_callback' => ['huh.list.backend.content', 'getListPreselectChoices'],
        'eval'             => ['tl_class' => 'wizard clr', 'mandatory' => true, 'includeBlankOption' => true, 'multiple' => true],
        'sql'              => "blob NULL",
    ],
];

$dc['fields'] = array_merge($dc['fields'], $fields);
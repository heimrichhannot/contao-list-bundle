<?php

$dca = &$GLOBALS['TL_DCA']['tl_module'];

/**
 * Palettes
 */
$dca['palettes'][\HeimrichHannot\ListBundle\Module\ModuleList::TYPE] =
    '{title_legend},name,headline,type;' . '{config_legend},listConfig;' . '{template_legend:hide},customTpl;'
    . '{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

/**
 * Fields
 */
$fields = [
    'listConfig' => [
        'label'      => &$GLOBALS['TL_LANG']['tl_module']['listConfig'],
        'exclude'    => true,
        'filter'     => true,
        'inputType'  => 'select',
        'foreignKey' => 'tl_list_config.title',
        'relation'   => ['type' => 'belongsTo', 'load' => 'lazy'],
        'eval'       => ['tl_class' => 'long clr', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
        'sql'        => "int(10) unsigned NOT NULL default '0'"
    ],
];

$dca['fields'] += $fields;

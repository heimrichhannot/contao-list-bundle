<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\ListBundle\Controller\FrontendModule\ListFrontendModuleController;
use HeimrichHannot\ListBundle\DataContainer\ModuleContainer;

$dca = &$GLOBALS['TL_DCA']['tl_module'];

/*
 * Palettes
 */
$dca['palettes'][ListFrontendModuleController::TYPE] =
    '{title_legend},name,headline,type;'.'{config_legend},listConfig;'.'{template_legend:hide},customTpl;'
    .'{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

/**
 * Fields.
 */
$fields = [
    'listConfig' => [
        'label' => &$GLOBALS['TL_LANG']['tl_module']['listConfig'],
        'exclude' => true,
        'filter' => true,
        'inputType' => 'select',
        'foreignKey' => 'tl_list_config.title',
        'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        'eval' => ['tl_class' => 'wizard clr', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
        'wizard' => [
            [ModuleContainer::class, 'editListConfigurationWizard'],
        ],
        'sql' => "int(10) unsigned NOT NULL default '0'",
    ],
];

$dca['fields'] += $fields;

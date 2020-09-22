<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$dc = &$GLOBALS['TL_DCA']['tl_content'];

$dc['config']['onload_callback'][] = [\HeimrichHannot\ListBundle\DataContainer\ContentContainer::class, 'onLoad'];

/*
 * Palettes
 */
$dc['palettes']['__selector__'][] = 'list';
$dc['palettes']['list_preselect'] = '{type_legend},type,headline;{huh.list_legend},listConfig;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID,guests;{invisible_legend:hide},invisible,start,stop';

/**
 * Fields.
 */
$fields = [
    'listConfig' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['listConfig'],
        'exclude' => true,
        'inputType' => 'select',
        'options_callback' => [\HeimrichHannot\ListBundle\DataContainer\ContentContainer::class, 'getListPreselectListConfigs'],
        'eval' => ['tl_class' => 'w50 clr', 'mandatory' => true, 'submitOnChange' => true, 'includeBlankOption' => true, 'chosen' => true],
        'sql' => "int(10) NOT NULL default '0'",
    ],
    'listPreselect' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['listPreselect'],
        'exclude' => true,
        'inputType' => 'checkboxWizard',
        'options_callback' => [\HeimrichHannot\ListBundle\DataContainer\ContentContainer::class, 'getListPreselectChoices'],
        'eval' => ['tl_class' => 'wizard clr', 'includeBlankOption' => true, 'multiple' => true],
        'sql' => 'blob NULL',
    ],
];

$dc['fields'] = array_merge($dc['fields'], $fields);

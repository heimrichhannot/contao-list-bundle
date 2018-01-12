<?php

$dca = &$GLOBALS['TL_DCA']['tl_user'];

/**
 * Palettes
 */
$dca['palettes']['extend'] = str_replace('fop;', 'fop;{list-bundle_legend},listbundles,listbundlep;', $dca['palettes']['extend']);
$dca['palettes']['custom'] = str_replace('fop;', 'fop;{list-bundle_legend},listbundles,listbundlep;', $dca['palettes']['custom']);

/**
 * Fields
 */
$dca['fields']['listbundles'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_user']['listbundles'],
    'exclude'    => true,
    'inputType'  => 'checkbox',
    'foreignKey' => 'tl_list_config.title',
    'eval'       => ['multiple' => true],
    'sql'        => "blob NULL"
];

$dca['fields']['listbundlep'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user']['listbundlep'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => ['multiple' => true],
    'sql'       => "blob NULL"
];

<?php

$dca = &$GLOBALS['TL_DCA']['tl_filter_config'];

$dca['palettes']['__selector__'][]       = 'asyncFormSubmit';
$dca['subpalettes']['asyncFormSubmit'] = 'ajaxList';

$dca['fields']['asyncFormSubmit']['eval']['submitOnChange'] = true;

$dca['fields']['ajaxList'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['ajaxList'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => ['huh.list.datacontainer.module', 'getAllListModules'],
    'eval' => ['tl_class' => 'w50 clr', 'includeBlankOption' => true, 'mandatory' => true],
    'sql' => "char(64) NOT NULL default ''",
];

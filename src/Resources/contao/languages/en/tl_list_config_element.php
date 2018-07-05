<?php

$lang = &$GLOBALS['TL_LANG']['tl_list_config_element'];

/**
 * Fields
 */
$lang['title'] = ['Title', 'Please enter a title.'];
$lang['tstamp'] = ['Revision date', ''];

$lang['typeSelectorField'][0]     = 'Selector-Field';
$lang['typeSelectorField'][1]     = 'Choose the field, which contains the boolean selector for the type.';
$lang['typeField'][0]             = 'Field';
$lang['typeField'][1]             = 'Choose the field containing the reference for the type.';
/**
 * Legends
 */
$lang['title_type_legend'] = 'Title and type';
$lang['config_legend']     = 'Configuration';

/**
 * Reference
 */
$lang['reference'] = [
    \HeimrichHannot\ListBundle\ConfigElementType\ImageConfigElementType::TYPE => 'Image',
    \HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE   => 'simple',
    \HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED => 'gendered',
];

/**
 * Buttons
 */
$lang['new'] = ['New Listenkonfigurations-Element', 'Listenkonfigurations-Element create'];
$lang['edit'] = ['Edit Listenkonfigurations-Element', 'Edit Listenkonfigurations-Element ID %s'];
$lang['copy'] = ['Duplicate Listenkonfigurations-Element', 'Duplicate Listenkonfigurations-Element ID %s'];
$lang['delete'] = ['Delete Listenkonfigurations-Element', 'Delete Listenkonfigurations-Element ID %s'];
$lang['toggle'] = ['Publish/unpublish Listenkonfigurations-Element', 'Publish/unpublish Listenkonfigurations-Element ID %s'];
$lang['show'] = ['Listenkonfigurations-Element details', 'Show the details of Listenkonfigurations-Element ID %s'];

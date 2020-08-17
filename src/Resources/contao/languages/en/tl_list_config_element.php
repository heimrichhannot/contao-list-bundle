<?php

$lang = &$GLOBALS['TL_LANG']['tl_list_config_element'];

/**
 * Fields
 */
$lang['title'] = ['Title', 'Please enter a title.'];
$lang['tstamp'] = ['Revision date', ''];

/**
 * Legends
 */
$lang['general_legend']  = 'General settings';
$lang['config_legend']   = 'Configuration';
$lang['template_legend'] = 'Template';

/**
 * Buttons
 */
$lang['new'] = ['New Listenkonfigurations-Element', 'Listenkonfigurations-Element create'];
$lang['edit'] = ['Edit Listenkonfigurations-Element', 'Edit Listenkonfigurations-Element ID %s'];
$lang['copy'] = ['Duplicate Listenkonfigurations-Element', 'Duplicate Listenkonfigurations-Element ID %s'];
$lang['delete'] = ['Delete Listenkonfigurations-Element', 'Delete Listenkonfigurations-Element ID %s'];
$lang['toggle'] = ['Publish/unpublish Listenkonfigurations-Element', 'Publish/unpublish Listenkonfigurations-Element ID %s'];
$lang['show'] = ['Listenkonfigurations-Element details', 'Show the details of Listenkonfigurations-Element ID %s'];

/**
 * Reference
 */
$lang['reference'][\HeimrichHannot\ListBundle\ConfigElementType\ImageConfigElementType::TYPE] = 'Image';
$lang['reference'][\HeimrichHannot\ListBundle\ConfigElementType\SubmissionFormConfigElementType::TYPE] = 'Submission form';
$lang['reference'][\HeimrichHannot\ListBundle\ConfigElementType\RelatedConfigElementType::getType()] = 'Related instances';
$lang['reference'][\HeimrichHannot\ListBundle\ConfigElementType\TagsConfigElementType::getType()] = 'Tags';

$lang['reference'][\HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE] = 'simple';
$lang['reference'][\HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED] = 'gendered';
$lang['reference'][\HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_RANDOM] = 'random';
$lang['reference'][\HeimrichHannot\ListBundle\DataContainer\ListConfigElementContainer::RELATED_CRITERIUM_TAGS] = 'tags';
$lang['reference'][\HeimrichHannot\ListBundle\DataContainer\ListConfigElementContainer::RELATED_CRITERIUM_CATEGORIES] = 'categories';

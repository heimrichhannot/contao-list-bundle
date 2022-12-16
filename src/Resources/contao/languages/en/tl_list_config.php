<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\ListBundle\ListExtension\DcMultilingualListExtension;

$lang = &$GLOBALS['TL_LANG']['tl_list_config'];

/*
 * Fields
 */
$lang['title'] = ['Title', 'Please enter a title.'];
$lang['tstamp'] = ['Revision date', ''];
$lang['listContextVariables'][0] = 'Custom variables';
$lang['listContextVariables'][1] = 'Enter custom variables that can be used in templates and events.';

// Extensions
$lang['use'.DcMultilingualListExtension::getAlias()] = [
    'Add support for "DC_Multilingual"',
    'Choose this option if the linked entity is translatable through "terminal42/contao-DC_Multilingual".',
];
$lang['dcMultilingualUseFallbackLang'][0] = 'Use Fallback language';
$lang['dcMultilingualUseFallbackLang'][1] = 'Choose this option if entities should be displayed in the fallback language if they are not available in the current language. Otherwise only translated records will be displayed';

/*
 * Legends
 */
$lang['general_legend'] = 'General settings';
$lang['extension_legend'] = 'List extensions';

/*
 * Buttons
 */
$lang['new'] = ['New Listenkonfiguration', 'Listenkonfiguration create'];
$lang['edit'] = ['Edit Listenkonfiguration', 'Edit Listenkonfiguration ID %s'];
$lang['copy'] = ['Duplicate Listenkonfiguration', 'Duplicate Listenkonfiguration ID %s'];
$lang['delete'] = ['Delete Listenkonfiguration', 'Delete Listenkonfiguration ID %s'];
$lang['toggle'] = ['Publish/unpublish Listenkonfiguration', 'Publish/unpublish Listenkonfiguration ID %s'];
$lang['show'] = ['Listenkonfiguration details', 'Show the details of Listenkonfiguration ID %s'];

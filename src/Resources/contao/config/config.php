<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\ListBundle\EventSubscriber\ReaderBundleEventSubscriber;

$GLOBALS['BE_MOD']['system']['list_configs'] = [
    'tables' => ['tl_list_config', 'tl_list_config_element'],
];

/*
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'listbundles';
$GLOBALS['TL_PERMISSIONS'][] = 'listbundlep';

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_list_config'] = 'HeimrichHannot\ListBundle\Model\ListConfigModel';
$GLOBALS['TL_MODELS']['tl_list_config_element'] = 'HeimrichHannot\ListBundle\Model\ListConfigElementModel';

/*
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadDataContainer']['huh_list'] = [ReaderBundleEventSubscriber::class, 'onLoadDataContainer'];

/*
 * Content elements
 */
$GLOBALS['TL_CTE']['huh.list']['list_preselect'] = \HeimrichHannot\ListBundle\ContentElement\ContentListPreselect::class;

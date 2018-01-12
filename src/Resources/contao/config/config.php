<?php

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['system']['list_configs'] = [
    'tables' => ['tl_list_config']
];

/**
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'listbundles';
$GLOBALS['TL_PERMISSIONS'][] = 'listbundlep';

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_list_config']         = 'HeimrichHannot\ListBundle\Model\ListConfigModel';
$GLOBALS['TL_MODELS']['tl_list_config_element'] = 'HeimrichHannot\ListBundle\Model\ListConfigElementModel';
<?php

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['system']['list_configs'] = [
    'tables' => ['tl_list_config']
];

/**
 * Frontend modules
 */
array_insert(
    $GLOBALS['FE_MOD']['list'],
    3,
    [
        \HeimrichHannot\ListBundle\Backend\Module::MODULE_LIST => 'HeimrichHannot\ListBundle\Module\ModuleList',
    ]
);

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
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
 * JS
 */
if (System::getContainer()->get('huh.utils.container')->isFrontend())
{
    $GLOBALS['TL_JAVASCRIPT']['list-bundle'] = 'bundles/heimrichhannotcontaolist/js/jquery.list-bundle.js|static';
    $GLOBALS['TL_JAVASCRIPT']['huh_list_masonry'] = 'assets/masonry/dist/masonry.pkgd.min.js|static';
    $GLOBALS['TL_JAVASCRIPT']['huh_list_imagesloaded'] = 'assets/imagesloaded/dist/imagesloaded.pkgd.min.js|static';
}

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


/**
 * Assets
 */


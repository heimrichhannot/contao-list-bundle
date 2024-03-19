<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use Contao\Controller;
use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\FilterBundle\Util\TwigSupportPolyfill\TwigTemplateLocator;
use HeimrichHannot\ListBundle\Choice\ListChoices;
use HeimrichHannot\ListBundle\DataContainer\ListConfigContainer;
use HeimrichHannot\ListBundle\Util\ListConfigHelper;

$GLOBALS['TL_DCA']['tl_list_config'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ptable' => 'tl_list_config',
        'ctable' => ['tl_list_config_element'],
        'enableVersioning' => true,
        'onload_callback' => [
            ['HeimrichHannot\ListBundle\Backend\ListConfig', 'flattenPaletteForSubEntities'],
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback' => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list' => [
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting' => [
            'mode' => 5,
            'fields' => ['title'],
            'headerFields' => ['title'],
            'panelLayout' => 'filter;sort,search,limit',
            'paste_button_callback' => [ListConfigContainer::class, 'pasteListConfig'],
        ],
        'global_operations' => [
            'toggleNodes' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['toggleAll'],
                'href' => 'ptg=all',
                'class' => 'header_toggle',
            ],
            'sortAlphabetically' => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config']['sortAlphabetically'],
                'href' => 'key=sort_alphabetically',
                'class' => 'header_toggle',
                'button_callback' => [ListConfigContainer::class, 'sortAlphabetically'],
            ],
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config']['edit'],
                'href' => 'table=tl_list_config_element',
                'icon' => 'edit.svg',
                'button_callback' => ['HeimrichHannot\ListBundle\Backend\ListConfig', 'edit'],
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config']['editheader'],
                'href' => 'act=edit',
                'icon' => 'header.svg',
                'button_callback' => ['HeimrichHannot\ListBundle\Backend\ListConfig', 'editHeader'],
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
            ],
            'copyChilds' => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config']['copyChilds'],
                'href' => 'act=paste&amp;mode=copy&amp;childs=1',
                'icon' => 'copychilds.gif',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
            'cut' => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config']['cut'],
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => [
            'showItemCount',
            'showNoItemsText',
            'limitFormattedFields',
            'isTableList',
            'sortingMode',
            'useAlias',
            'addDetails',
            'openListItemsInModal',
            'listModalReaderType',
            'addShare',
            'addAjaxPagination',
            'addMasonry',
            'addOverview',
            'customJumpToOverviewLabel',
        ],
        'default' => '{general_legend},title;'.'{filter_legend},filter;'.'{config_legend},manager,list,item,numberOfItems,perPage,skipFirst,doNotRenderEmpty,showItemCount,showNoItemsText,showInitialResults,limitFormattedFields,isTableList;'.'{sorting_legend},sortingMode;'.'{jumpto_legend},useAlias,addDetails,addShare,addOverview;'.'{action_legend},addHashToAction,removeAutoItemFromAction;'.'{misc_legend},addAjaxPagination,addMasonry,addDcMultilingualSupport,addMultilingualFieldsSupport,hideForListPreselect,listContextVariables;'.'{search_legend},noSearch,doNotIndexItems;'.'{template_legend},listTemplate,itemTemplate,itemChoiceTemplate;',
    ],
    'subpalettes' => [
        'showItemCount' => 'itemCountText',
        'showNoItemsText' => 'noItemsText',
        'limitFormattedFields' => 'formattedFields',
        'isTableList' => 'tableFields,hasHeader,sortingHeader',
        'sortingMode_'.\HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_MODE_FIELD => 'sortingField,sortingDirection',
        'sortingMode_'.\HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_MODE_TEXT => 'sortingText',
        'sortingMode_'.\HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_MODE_MANUAL => 'sortingItems',
        'useAlias' => 'aliasField',
        'addDetails' => 'jumpToDetails,jumpToDetailsMultilingual,openListItemsInModal',
        'openListItemsInModal' => 'listModalTemplate,listModalReaderType',
        'listModalReaderType_css' => 'listModalReaderCssSelector',
        'listModalReaderType_huh_reader' => 'listModalReaderModule',
        'addShare' => 'jumpToShare,shareAutoItem',
        'addAjaxPagination' => 'ajaxPaginationTemplate,addInfiniteScroll',
        'addMasonry' => 'masonryStampContentElements',
        'addOverview' => 'jumpToOverview,jumpToOverviewMultilingual,customJumpToOverviewLabel',
        'customJumpToOverviewLabel' => 'jumpToOverviewLabel',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
            'eval' => ['notOverridable' => true],
        ],
        'tstamp' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['tstamp'],
            'eval' => ['notOverridable' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting' => [
            'eval' => ['notOverridable' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag' => 6,
            'eval' => ['rgxp' => 'datim', 'doNotCopy' => true, 'notOverridable' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        // general
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['title'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'notOverridable' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'pid' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['pid'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'sorting' => true,
            'options_callback' => [ListChoices::class, 'getParentListConfigOptions'],
            'wizard' => [
                ['HeimrichHannot\ListBundle\Backend\ListConfig', 'editList'],
            ],
            'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true, 'chosen' => true, 'notOverridable' => true, 'submitOnChange' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        // config
        'manager' => [
            'inputType' => 'select',
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['manager'],
            'options_callback' => ['huh.list.choice.manager', 'getChoices'],
            'eval' => [
                'chosen' => true,
                'includeBlankOption' => true,
                'tl_class' => 'clr w50',
                'mandatory' => true,
            ],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default 'default'",
        ],
        'list' => [
            'inputType' => 'select',
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['list'],
            'options_callback' => ['huh.list.choice.list', 'getChoices'],
            'search' => true,
            'filter' => true,
            'eval' => [
                'chosen' => true,
                'includeBlankOption' => true,
                'mandatory' => true,
                'tl_class' => 'w50',
            ],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default 'default'",
        ],
        'item' => [
            'inputType' => 'select',
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['item'],
            'options_callback' => ['huh.list.choice.item', 'getChoices'],
            'search' => true,
            'filter' => true,
            'eval' => [
                'chosen' => true,
                'includeBlankOption' => true,
                'mandatory' => true,
                'tl_class' => 'w50',
            ],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default 'default'",
        ],
        'limitFormattedFields' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['limitFormattedFields'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'formattedFields' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['formattedFields'],
            'inputType' => 'checkboxWizard',
            'options_callback' => function (DataContainer $dc) {
                return ListConfigHelper::getFields($dc);
            },
            'exclude' => true,
            'eval' => ['multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 clr autoheight'],
            'sql' => 'blob NULL',
        ],
        'numberOfItems' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['numberOfItems'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => 'smallint(5) unsigned NOT NULL default 3',
        ],
        'perPage' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['perPage'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => 'smallint(5) unsigned NOT NULL default 0',
        ],
        'skipFirst' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['skipFirst'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => 'smallint(5) unsigned NOT NULL default 0',
        ],
        'showItemCount' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['showItemCount'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'itemCountText' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['itemCountText'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => function (DataContainer $dc) {
                return ListChoices::getMessageOptions($dc, 'huh.list.count.text');
            },
            'eval' => ['maxlength' => 64, 'includeBlankOption' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'showNoItemsText' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['showNoItemsText'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'noItemsText' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['noItemsText'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'select',
            'options_callback' => function (DataContainer $dc) {
                return ListChoices::getMessageOptions($dc, 'huh.list.empty.text');
            },
            'eval' => ['maxlength' => 64, 'tl_class' => 'w50', 'includeBlankOption' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'showInitialResults' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['showInitialResults'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default '1'",
        ],
        'doNotRenderEmpty' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['doNotRenderEmpty'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'isTableList' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['isTableList'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'hasHeader' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['hasHeader'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'sortingHeader' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['sortingHeader'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'tableFields' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['tableFields'],
            'inputType' => 'checkboxWizard',
            'options_callback' => function (DataContainer $dc) {
                return ListConfigHelper::getFields($dc);
            },
            'exclude' => true,
            'eval' => ['multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 clr autoheight'],
            'sql' => 'blob NULL',
        ],
        // filter
        'filter' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['filter'],
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_filter_config.title',
            'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
            'eval' => ['tl_class' => 'w50 clr wizard', 'includeBlankOption' => true, 'chosen' => true, 'mandatory' => true, 'submitOnChange' => true],
            'wizard' => [
                ['HeimrichHannot\ListBundle\Backend\ListConfig', 'editFilter'],
            ],
            'sql' => "int(10) NOT NULL default '0'",
        ],
        // sorting
        'sortingMode' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['sortingMode'],
            'exclude' => true,
            'inputType' => 'select',
            'options' => \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_MODES,
            'reference' => &$GLOBALS['TL_LANG']['tl_list_config']['reference'],
            'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql' => "varchar(32) NOT NULL default 'field'",
        ],
        'sortingField' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['sortingField'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'select',
            'options_callback' => function (DataContainer $dc) {
                return ListConfigHelper::getFields($dc);
            },
            'reference' => &$GLOBALS['TL_LANG']['tl_list_config']['reference'],
            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'sortingDirection' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['sortingDirection'],
            'exclude' => true,
            'inputType' => 'select',
            'options' => \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_DIRECTIONS,
            'reference' => &$GLOBALS['TL_LANG']['tl_list_config']['reference'],
            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'sortingText' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['sortingText'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true, 'decodeEntities' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'sortingItems' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['sortingItems'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkboxWizard',
            'options_callback' => function (DataContainer $dc) {
                return ListConfigContainer::getModelInstances($dc);
            },
            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'multiple' => true],
            'sql' => 'blob NULL',
        ],
        // jump to
        'useAlias' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['useAlias'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'aliasField' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['aliasField'],
            'exclude' => true,
            'filter' => false,
            'search' => true,
            'inputType' => 'select',
            'options_callback' => function (DataContainer $dc) {
                return ListConfigContainer::getFields($dc);
            },
            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'addDetails' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['addDetails'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 clr', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'jumpToDetails' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['jumpToDetails'],
            'exclude' => true,
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'eager'],
        ],
        'jumpToDetailsMultilingual' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['jumpToDetailsMultilingual'],
            'inputType' => 'multiColumnEditor',
            'eval' => [
                'tl_class' => 'long clr',
                'multiColumnEditor' => [
                    'minRowCount' => 0,
                    'fields' => [
                        'language' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['jumpToDetailsMultilingual']['language'],
                            'inputType' => 'select',
                            'options' => System::getContainer()->get('contao.intl.locales')->getLanguages(),
                            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'groupStyle' => 'width: 400px;'],
                        ],
                        'jumpTo' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['jumpToDetailsMultilingual']['jumpTo'],
                            'inputType' => 'pageTree',
                            'foreignKey' => 'tl_page.title',
                            'eval' => ['fieldType' => 'radio', 'tl_class' => 'w50', 'mandatory' => true],
                            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
                        ],
                    ],
                ],
            ],
            'sql' => 'blob NULL',
        ],
        'openListItemsInModal' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['openListItemsInModal'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'submitOnChange' => true, 'addAsDataAttribute' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'listModalTemplate' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['listModalTemplate'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => function (DataContainer $dc) {
                return System::getContainer()->get(TwigTemplateLocator::class)->getTemplateGroup('list_modal_');
            },
            'eval' => ['tl_class' => 'long clr', 'includeBlankOption' => true, 'chosen' => true],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'listModalReaderType' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['listModalReaderType'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => [
                'css_selector',
                'huh_reader',
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_list_config']['reference'],
            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true, 'addAsDataAttribute' => true],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'listModalReaderCssSelector' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['listModalReaderCssSelector'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 128, 'tl_class' => 'w50', 'mandatory' => true, 'addAsDataAttribute' => true],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'listModalReaderModule' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['listModalReaderModule'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => function (DataContainer $dc) {
                return ListChoices::getModelInstanceOptions($dc, 'tl_module', labelPattern: '%name% (ID %id%)');
            },
            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'addAsDataAttribute' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'addShare' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['addShare'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 clr', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'jumpToShare' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['jumpToShare'],
            'exclude' => true,
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval' => ['fieldType' => 'radio', 'tl_class' => 'w50 clr'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'eager'],
        ],
        'shareAutoItem' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['shareAutoItem'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        // misc
        'addAjaxPagination' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['addAjaxPagination'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'ajaxPaginationTemplate' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['ajaxPaginationTemplate'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => function (DataContainer $dc) {
                return Controller::getTemplateGroup('pagination');
            },
            'eval' => ['tl_class' => 'w50 clr', 'includeBlankOption' => true, 'chosen' => true],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'addInfiniteScroll' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['addInfiniteScroll'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'addAsDataAttribute' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'addMasonry' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['addMasonry'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 clr', 'submitOnChange' => true, 'addAsDataAttribute' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'masonryStampContentElements' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['masonryStampContentElements'],
            'inputType' => 'multiColumnEditor',
            'eval' => [
                'multiColumnEditor' => [
                    'minRowCount' => 0,
                    'fields' => [
                        'stampBlock' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['stampBlock'],
                            'exclude' => true,
                            'inputType' => 'select',
                            'options_callback' => ['HeimrichHannot\Blocks\Backend\Content', 'getBlocks'],
                            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
                        ],
                        'stampCssClass' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['stampCssClass'],
                            'exclude' => true,
                            'search' => true,
                            'inputType' => 'text',
                            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
                        ],
                    ],
                ],
            ],
            'sql' => 'blob NULL',
        ],
        'hideForListPreselect' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['hideForListPreselect'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        // template
        'listTemplate' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['listTemplate'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => [ListConfigContainer::class, 'onListTemplateOptionsCallback'],
            'eval' => ['tl_class' => 'long clr', 'includeBlankOption' => true, 'chosen' => true],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'itemTemplate' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['itemTemplate'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => [ListConfigContainer::class, 'onItemTemplateOptionsCallback'],
            'eval' => ['tl_class' => 'long clr', 'includeBlankOption' => true, 'chosen' => true],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'itemChoiceTemplate' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['itemChoiceTemplate'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => [ListConfigContainer::class, 'onItemChoiceTemplateOptionsCallback'],
            'eval' => ['tl_class' => 'long clr', 'includeBlankOption' => true, 'chosen' => true],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'noSearch' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['noSearch'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'default' => true,
            'search' => true,
            'eval' => ['tl_class' => 'w50 clr'],
            'sql' => "char(1) NOT NULL default '1'",
        ],
        'doNotIndexItems' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['doNotIndexItems'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'search' => true,
            'default' => true,
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default '1'",
        ],
        'addOverview' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['addOverview'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'search' => true,
            'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'jumpToOverview' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['jumpToOverview'],
            'exclude' => true,
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr', 'mandatory' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'eager'],
        ],
        'jumpToOverviewMultilingual' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['jumpToOverviewMultilingual'],
            'inputType' => 'multiColumnEditor',
            'eval' => [
                'tl_class' => 'long clr',
                'multiColumnEditor' => [
                    'minRowCount' => 0,
                    'fields' => [
                        'language' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['jumpToDetailsMultilingual']['language'],
                            'inputType' => 'select',
                            'options' => System::getContainer()->get('contao.intl.locales')->getLanguages(),
                            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'groupStyle' => 'width: 400px;'],
                        ],
                        'jumpTo' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['jumpToDetailsMultilingual']['jumpTo'],
                            'inputType' => 'pageTree',
                            'foreignKey' => 'tl_page.title',
                            'eval' => ['fieldType' => 'radio', 'tl_class' => 'w50', 'mandatory' => true],
                            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
                        ],
                    ],
                ],
            ],
            'sql' => 'blob NULL',
        ],
        'customJumpToOverviewLabel' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['customJumpToOverviewLabel'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'jumpToOverviewLabel' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['jumpToOverviewLabel'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => function (DataContainer $dc) {
                return ListChoices::getMessageOptions($dc, 'huh.list.labels.overview');
            },
            'eval' => ['chosen' => true, 'mandatory' => true, 'maxlength' => 128, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'listContextVariables' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['listContextVariables'],
            'inputType' => 'keyValueWizard',
            'exclude' => true,
            'eval' => ['tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
    ],
];

$dca = &$GLOBALS['TL_DCA']['tl_list_config'];

$dca['fields']['numberOfItems']['eval']['tl_class'] = 'w50 clr';

if (in_array('modal', array_keys(System::getContainer()->getParameter('kernel.bundles')))) {
    $dca['fields']['useModal'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_list_config']['useModal'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'eval' => ['tl_class' => 'w50 clr'],
        'sql' => "char(1) NOT NULL default ''",
    ];

    $dca['fields']['useModalExplanation'] = [
        'inputType' => 'explanation',
        'eval' => [
            'notOverridable' => true,
            'text' => &$GLOBALS['TL_LANG']['tl_list_config']['useModalExplanation'],
            'class' => 'tl_info',
            'collapsible' => true,
            'tl_class' => 'clr long',
        ],
    ];

    $dca['subpalettes']['addDetails'] = 'useModalExplanation,useModal,'.$dca['subpalettes']['addDetails'];
}

if (in_array('Terminal42\DcMultilingualBundle\Terminal42DcMultilingualBundle',
             System::getContainer()->getParameter('kernel.bundles')))
{
    $dca['fields'] = array_merge(is_array($dca['fields']) ? $dca['fields'] : [], [
        'addDcMultilingualSupport' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['addDcMultilingualSupport'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ]);
}

if (class_exists('HeimrichHannot\MultilingualFieldsBundle\HeimrichHannotMultilingualFieldsBundle')) {
    $dca['fields'] = array_merge(is_array($dca['fields']) ? $dca['fields'] : [], [
        'addMultilingualFieldsSupport' => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['addMultilingualFieldsSupport'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ]);
}

\HeimrichHannot\ListBundle\Backend\ListConfig::addOverridableFields();

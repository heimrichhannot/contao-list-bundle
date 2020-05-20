<?php

$GLOBALS['TL_DCA']['tl_list_config_element'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'ptable'            => 'tl_list_config',
        'enableVersioning'  => true,
        'onload_callback'   => [
            [\HeimrichHannot\ListBundle\DataContainer\ListConfigElementContainer::class, 'onLoadCallback']
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback'   => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql'               => [
            'keys' => [
                'id'       => 'primary',
                'type,pid' => 'index',
            ],
        ],
    ],
    'list'        => [
        'label'             => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting'           => [
            'mode'                  => 4,
            'fields'                => ['title'],
            'headerFields'          => ['title'],
            'panelLayout'           => 'filter;sort,search,limit',
            'child_record_callback' => [\HeimrichHannot\ListBundle\DataContainer\ListConfigElementContainer::class, 'listChildren'],
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config_element']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config_element']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_list_config_element']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config_element']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],
    'palettes'    => [
        '__selector__' => [
            'type',
            'placeholderImageMode',
            'tagsAddLink'
        ],
        'default'      => '{title_type_legend},title,type;',
    ],
    'subpalettes' => [
        'placeholderImageMode_' . \HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE   => 'placeholderImage',
        'placeholderImageMode_' . \HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED => 'genderField,placeholderImage,placeholderImageFemale',
        'placeholderImageMode_' . \HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_RANDOM   => 'placeholderImages',
        'placeholderImageMode_' . \HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_FIELD    => 'fieldDependentPlaceholderConfig',
        'tagsAddLink'                                                                                                   => 'tagsFilter,tagsFilterConfigElement,tagsJumpTo'
    ],
    'fields'      => [
        'id'                              => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'                             => [
            'foreignKey' => 'tl_list_config.title',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'tstamp'                          => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config_element']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded'                       => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'",
        ],
        'title'                           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['title'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'type'                            => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config_element']['type'],
            'exclude'          => true,
            'filter'           => true,
            'sorting'          => true,
            'inputType'        => 'select',
            'options_callback' => [\HeimrichHannot\ListBundle\DataContainer\ListConfigElementContainer::class, 'getConfigElementTypes'],
            'reference'        => &$GLOBALS['TL_LANG']['tl_list_config_element']['reference'],
            'eval'             => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true, 'chosen' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'templateVariable'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['templateVariable'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'imageSelectorField'              => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config_element']['imageSelectorField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return \HeimrichHannot\ListBundle\Util\ListConfigElementHelper::getCheckboxFields($dc);
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'tl_class' => 'w50 autoheight', 'chosen' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'imageField'                      => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config_element']['imageField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return \HeimrichHannot\ListBundle\Util\ListConfigElementHelper::getFields($dc);
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50 autoheight'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'imgSize'                         => [
            'exclude'          => true,
            'inputType'        => 'imageSize',
            'reference'        => &$GLOBALS['TL_LANG']['MSC'],
            'eval'             => ['rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
            'options_callback' => static function () {
                return Contao\System::getContainer()->get('contao.image.image_sizes')->getOptionsForUser(Contao\BackendUser::getInstance());
            },
            'sql'              => "varchar(255) NOT NULL default ''"
        ],
        'placeholderImageMode'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['placeholderImageMode'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => \HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODES,
            'reference' => &$GLOBALS['TL_LANG']['tl_list_config_element']['reference'],
            'eval'      => ['tl_class' => 'w50', 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'placeholderImage'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['placeholderImage'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['tl_class' => 'w50 autoheight', 'fieldType' => 'radio', 'filesOnly' => true, 'extensions' => Config::get('validImageTypes'), 'mandatory' => true],
            'sql'       => "binary(16) NULL",
        ],
        'placeholderImageFemale'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['placeholderImageFemale'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['tl_class' => 'w50 autoheight', 'fieldType' => 'radio', 'filesOnly' => true, 'extensions' => Config::get('validImageTypes'), 'mandatory' => true],
            'sql'       => "binary(16) NULL",
        ],
        'genderField'                     => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config_element']['genderField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return \HeimrichHannot\ListBundle\Util\ListConfigElementHelper::getFields($dc);
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50 autoheight'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'placeholderImages'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['placeholderImages'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['tl_class' => 'w50 autoheight', 'fieldType' => 'checkbox', 'filesOnly' => true, 'extensions' => Config::get('validImageTypes'), 'mandatory' => true, 'multiple' => true],
            'sql'       => "blob NULL",
        ],
        'submissionFormExplanation'       => [
            'inputType' => 'explanation',
            'eval'      => [
                'text'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['submissionFormExplanation'],
                'class'    => 'tl_info',
                'tl_class' => 'long',
            ]
        ],
        'submissionReader'                => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config_element']['submissionReader'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'reference'        => &$GLOBALS['TL_LANG']['tl_']['reference'],
            'options_callback' => function () {
                return System::getContainer()->get('huh.utils.choice.model_instance')->getCachedChoices([
                    'dataContainer' => 'tl_module'
                ]);
            },
            'eval'             => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
            'sql'              => "varchar(64) NOT NULL default ''"
        ],
        'emailField'                      => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config_element']['emailField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return \HeimrichHannot\ListBundle\Util\ListConfigElementHelper::getFields($dc);
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50 autoheight'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'fieldDependentPlaceholderConfig' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['fieldDependentPlaceholderConfig'],
            'inputType' => 'multiColumnEditor',
            'eval'      => [
                'tl_class'          => 'long clr',
                'multiColumnEditor' => [
                    'minRowCount' => 0,
                    'fields'      => [
                        'field'            => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_list_config_element']['fieldDependentPlaceholderConfig']['field'],
                            'inputType'        => 'select',
                            'options_callback' => function (DataContainer $dc) {
                                return \HeimrichHannot\ListBundle\Util\ListConfigElementHelper::getFields($dc);
                            },
                            'eval'             => ['style' => 'width: 200px', 'mandatory' => true, 'includeBlankOption' => true],
                        ],
                        'operator'         => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['fieldDependentPlaceholderConfig']['operator'],
                            'inputType' => 'select',
                            'options'   => \HeimrichHannot\UtilsBundle\Comparison\CompareUtil::PHP_OPERATORS,
                            'reference' => &$GLOBALS['TL_LANG']['MSC']['phpOperators'],
                            'eval'      => ['style' => 'width: 200px', 'mandatory' => true, 'includeBlankOption' => true],
                        ],
                        'value'            => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['fieldDependentPlaceholderConfig']['value'],
                            'inputType' => 'text',
                            'eval'      => ['style' => 'width: 200px'],
                        ],
                        'placeholderImage' => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['fieldDependentPlaceholderConfig']['placeholderImage'],
                            'exclude'   => true,
                            'inputType' => 'fileTree',
                            'eval'      => ['style' => 'width: 200px', 'tl_class' => 'w50 autoheight', 'fieldType' => 'radio', 'filesOnly' => true, 'extensions' => Config::get('validImageTypes'), 'mandatory' => true],
                        ]
                    ]
                ]
            ],
            'sql'       => "blob NULL"
        ],
        'relatedExplanation'              => [
            'inputType' => 'explanation',
            'eval'      => [
                'text'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['relatedExplanation'],
                'class'    => 'tl_info',
                'tl_class' => 'long clr',
            ]
        ],
        'relatedListModule'               => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config_element']['relatedListModule'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return System::getContainer()->get('huh.utils.choice.model_instance')->getCachedChoices([
                    'dataContainer' => 'tl_module',
                    'labelPattern'  => '%name% (ID %id%)'
                ]);
            },
            'eval'             => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
            'sql'              => "varchar(64) NOT NULL default ''"
        ],
        'relatedCriteriaExplanation'      => [
            'inputType' => 'explanation',
            'eval'      => [
                'text'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['relatedCriteriaExplanation'],
                'class'    => 'tl_info',
                'tl_class' => 'long clr',
            ]
        ],
        'relatedCriteria'                 => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config_element']['relatedCriteria'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'checkbox',
            'options_callback' => [\HeimrichHannot\ListBundle\DataContainer\ListConfigElementContainer::class, 'getRelatedCriteriaAsOptions'],
            'reference'        => &$GLOBALS['TL_LANG']['tl_list_config_element']['reference'],
            'eval'             => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'multiple' => true, 'submitOnChange' => true],
            'sql'              => "blob NULL"
        ],
        'tagsField'                     => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config_element']['tagsField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                if (!$dc->activeRecord->pid) {
                    return [];
                }

                if (null === ($listConfig = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_list_config', $dc->activeRecord->pid)) || !$listConfig->filter)
                {
                    return [];
                }

                if (null === ($filterConfig = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_filter_config', $listConfig->filter)) || !$filterConfig->dataContainer)
                {
                    return [];
                }

                return System::getContainer()->get('huh.utils.choice.field')->getCachedChoices([
                    'dataContainer' => $filterConfig->dataContainer,
                    'inputTypes' => ['cfgTags']
                ]);
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'tagsTemplate'             => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config_element']['tagsTemplate'],
            'exclude'          => true,
            'inputType'        => 'select',
            'default'          => 'config_element_tags_default.html',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.twig_template')->getCachedChoices(['config_element_tags_']);
            },
            'eval'             => ['tl_class' => 'w50', 'includeBlankOption' => true, 'mandatory' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'tagsAddLink' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_list_config_element']['tagsAddLink'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'                     => "char(1) NOT NULL default ''"
        ],
        'tagsFilter' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_list_config_element']['tagsFilter'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return System::getContainer()->get('huh.utils.choice.model_instance')->getCachedChoices([
                    'dataContainer' => 'tl_filter_config',
                    'labelPattern' => '%title% (ID %id%)'
                ]);
            },
            'eval'                    => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'submitOnChange' => true],
            'sql'                     => "varchar(64) NOT NULL default ''"
        ],
        'tagsFilterConfigElement' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_list_config_element']['tagsFilterConfigElement'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'options_callback' => function (\Contao\DataContainer $dc) {
                if (!$dc->activeRecord->tagsFilter) {
                    return [];
                }

                return System::getContainer()->get('huh.utils.choice.model_instance')->getCachedChoices([
                    'dataContainer' => 'tl_filter_config_element',
                    'columns' => [
                        'tl_filter_config_element.pid=?'
                    ],
                    'values' => [
                        $dc->activeRecord->tagsFilter
                    ],
                    'labelPattern' => '%title% (ID %id%)'
                ]);
            },
            'eval'                    => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
            'sql'                     => "varchar(64) NOT NULL default ''"
        ],
        'tagsJumpTo' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_list_config_element']['tagsJumpTo'],
            'exclude'                 => true,
            'inputType'               => 'pageTree',
            'foreignKey'              => 'tl_page.title',
            'eval'                    => ['fieldType'=>'radio', 'tl_class' => 'w50', 'mandatory' => true],
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => ['type'=>'hasOne', 'load'=>'lazy']
        ],
    ],
];

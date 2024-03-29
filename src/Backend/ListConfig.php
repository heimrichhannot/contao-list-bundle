<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Backend;

use Contao\Backend;
use Contao\BackendUser;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;

class ListConfig extends Backend
{
    const SORTING_MODE_FIELD  = 'field';
    const SORTING_MODE_TEXT   = 'text';
    const SORTING_MODE_RANDOM = 'random';
    const SORTING_MODE_MANUAL = 'manual';

    const SORTING_MODES = [
        self::SORTING_MODE_FIELD,
        self::SORTING_MODE_TEXT,
        self::SORTING_MODE_RANDOM,
        self::SORTING_MODE_MANUAL,
    ];

    const SORTING_DIRECTION_ASC  = 'asc';
    const SORTING_DIRECTION_DESC = 'desc';

    const SORTING_DIRECTIONS = [
        self::SORTING_DIRECTION_ASC,
        self::SORTING_DIRECTION_DESC,
    ];

    /**
     * Return the edit filter wizard.
     *
     * @return string
     */
    public function editFilter(DataContainer $dc)
    {
        $editFilterTitle =  $GLOBALS['TL_LANG']['tl_list_config']['editFilter'][0] ?? '';
        $editFilterDesc  = $GLOBALS['TL_LANG']['tl_list_config']['editFilter'][1] ?? '';
        return ($dc->value < 1) ? '' : ' <a href="contao/main.php?do=filter&amp;table=tl_filter_config_element&amp;id=' . $dc->value . '&amp;popup=1&amp;nb=1&amp;rt=' . REQUEST_TOKEN . '" title="' . sprintf(StringUtil::specialchars($editFilterDesc), $dc->value) . '" onclick="Backend.openModalIframe({\'title\':\'' . StringUtil::specialchars(str_replace("'", "\\'",
                sprintf($editFilterDesc, $dc->value))) . '\',\'url\':this.href});return false">' . Image::getHtml('alias.svg', $editFilterTitle) . '</a>';
    }

    public function editList(DataContainer $dc)
    {
        $editListTitle =  $GLOBALS['TL_LANG']['tl_filter_config']['edit'][0] ?? '';
        $editListDesc  = $GLOBALS['TL_LANG']['tl_list_config']['edit'][1] ?? '';
        return ($dc->value < 1) ? '' : ' <a href="contao/main.php?do=list_configs&amp;act=edit&amp;id=' . $dc->value . '&amp;rt=' . REQUEST_TOKEN . '" title="' . sprintf(StringUtil::specialchars($editListDesc), $dc->value) . '">' . Image::getHtml('alias.svg', $editListTitle) . '</a>';
    }

    public static function addOverridableFields()
    {
        $dca = &$GLOBALS['TL_DCA']['tl_list_config'];

        $overridableFields = [];

        foreach ($dca['fields'] as $field => $data) {
            $overrideFieldname = 'override' . ucfirst($field);

            if (isset($data['eval']['notOverridable']) || isset($dca['fields'][$overrideFieldname]) ||
                isset($data['eval']['isOverrideSelector'])) {
                continue;
            }

            $overridableFields[] = $field;
        }

        System::getContainer()->get('huh.utils.dca')->addOverridableFields(
            $overridableFields,
            'tl_list_config',
            'tl_list_config',
            [
                'checkboxDcaEvalOverride' => [
                    'tl_class' => 'w50 clr',
                ],
            ]
        );
    }

    public static function flattenPaletteForSubEntities(DataContainer $dc)
    {
        if (null !== ($listConfig = System::getContainer()->get('huh.list.list-config-registry')->findByPk($dc->id))) {
            if ($listConfig->pid) {
                $dca = &$GLOBALS['TL_DCA']['tl_list_config'];

                $overridableFields = [];

                foreach ($dca['fields'] as $field => $data) {
                    if (isset($data['eval']['notOverridable']) || isset($data['eval']['isOverrideSelector'])) {
                        continue;
                    }

                    $overridableFields[] = $field;
                }

                System::getContainer()->get('huh.utils.dca')->flattenPaletteForSubEntities('tl_list_config', $overridableFields);
            }
        }
    }

    /**
     * @param array
     * @param string
     * @param object
     * @param string
     *
     * @return string
     */
    public function generateLabel($row, $label, $dca, $attributes)
    {
        if ($row['pid']) {
            if (null !== ($listConfig = System::getContainer()->get('huh.list.list-config-registry')->findByPk($row['pid']))) {
                $label .= '<span style="padding-left:3px;color:#b3b3b3;">[' . $GLOBALS['TL_LANG']['MSC']['listBundle']['parentConfig'] . ': ' . $listConfig->title . ']</span>';
            }
        }

        return $label;
    }

    /**
     * Return the edit header button.
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function editHeader($row, $href, $label, $title, $icon, $attributes)
    {
        return BackendUser::getInstance()->canEditFieldsOf('tl_list_config') ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
    }

    public function edit($row, $href, $label, $title, $icon, $attributes)
    {
        if ($row['pid']) {
            return '';
        }

        return sprintf('<a href="%s" title="%s" class="edit">%s</a>', $this->addToUrl($href . '&amp;id=' . $row['id']), $title, Image::getHtml($icon, $label));
    }
}

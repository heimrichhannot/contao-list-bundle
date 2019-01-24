<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Util;

use Contao\DataContainer;
use Contao\System;

class ListConfigElementHelper
{
    public static function getFields(DataContainer $dc)
    {
        if (!$dc->id || null === ($filter = System::getContainer()->get('huh.list.list-config-element-registry')->getFilterByPk($dc->id))) {
            return [];
        }

        return System::getContainer()->get('huh.utils.choice.field')->getCachedChoices([
            'dataContainer' => $filter['dataContainer'],
        ]);
    }

    public static function getCheckboxFields(DataContainer $dc)
    {
        if (!$dc->id || null === ($filter = System::getContainer()->get('huh.list.list-config-element-registry')->getFilterByPk($dc->id))) {
            return [];
        }

        return System::getContainer()->get('huh.utils.choice.field')->getCachedChoices([
            'dataContainer' => $filter['dataContainer'],
            'inputTypes' => ['checkbox'],
        ]);
    }

    public static function getConfigElementTypes()
    {
        $types = [];

        $listConfig = System::getContainer()->getParameter('huh.list');
        $configElementTypes = $listConfig['list']['config_element_types'];

        foreach ($configElementTypes as $configElementType) {
            $types[] = $configElementType['name'];
        }

        return $types;
    }
}

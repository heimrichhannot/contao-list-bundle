<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Util;

use Contao\DataContainer;
use Contao\System;

class ListConfigHelper
{
    public static function getFields(DataContainer $dc)
    {
        $listConfigRegistry = System::getContainer()->get('huh.list.list-config-registry');

        if (null === ($listConfig = $listConfigRegistry->findByPk($dc->id))) {
            return [];
        }

        $listConfig = System::getContainer()->get('huh.utils.model')->findRootParentRecursively(
            'parentListConfig', 'tl_list_config', $listConfig
        );

        if (null === $listConfig || null === ($filter = $listConfigRegistry->getFilterByPk($listConfig->id))) {
            return [];
        }

        return \Contao\System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
            [
                'dataContainer' => $filter['dataContainer'],
            ]
        );
    }

    public static function getTextFields(DataContainer $dc)
    {
        $listConfigRegistry = System::getContainer()->get('huh.list.list-config-registry');

        if (null === ($listConfig = $listConfigRegistry->findByPk($dc->id))) {
            return [];
        }

        $listConfig = System::getContainer()->get('huh.utils.model')->findRootParentRecursively(
            'parentListConfig', 'tl_list_config', $listConfig
        );

        if (null === $listConfig || null === ($filter = $listConfigRegistry->getFilterByPk($listConfig->id))) {
            return [];
        }

        return \Contao\System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
            [
                'dataContainer' => $filter['dataContainer'],
                'inputTypes' => ['text'],
            ]
        );
    }

    public static function getModelInstances(DataContainer $dc)
    {
        $listConfigRegistry = System::getContainer()->get('huh.list.list-config-registry');

        if (null === ($listConfig = $listConfigRegistry->findByPk($dc->id))) {
            return [];
        }

        $listConfig = System::getContainer()->get('huh.utils.model')->findRootParentRecursively(
            'parentListConfig', 'tl_list_config', $listConfig
        );

        if (null === $listConfig || null === ($filter = $listConfigRegistry->getFilterByPk($listConfig->id))) {
            return [];
        }

        return \Contao\System::getContainer()->get('huh.utils.choice.model_instance')->getCachedChoices(
            [
                'dataContainer' => $filter['dataContainer'],
            ]
        );
    }
}

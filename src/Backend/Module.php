<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Backend;

use Contao\DataContainer;
use Contao\ModuleModel;
use Contao\System;
use HeimrichHannot\ListBundle\Module\ModuleList;

class Module
{
    /**
     * @deprecated use ModuleList::TYPE instead
     */
    const MODULE_LIST = 'huhlist';

    public function getAllListModules()
    {
        $listModules = [];
        /** @var ModuleModel $adapter */
        $modules = ModuleModel::findBy('type', ModuleList::TYPE);

        if (null === $modules) {
            return $listModules;
        }

        foreach ($modules as $module) {
            $listModules[$module->id] = $module->name;
        }

        return $listModules;
    }

    public function getFieldsByListModule(DataContainer $dc)
    {
        if (!$dc->id || null === ($readerConfigElement = System::getContainer()->get('huh.reader.reader-config-element-registry')->findByPk($dc->id))) {
            return [];
        }

        if ('' === $readerConfigElement->listModule || null === ($listModule = ModuleModel::findById($readerConfigElement->listModule))) {
            return [];
        }

        if (null === ($listConfig = System::getContainer()->get('huh.list.list-config-registry')->findByPk($listModule->listConfig))) {
            return [];
        }

        if (null === ($filterConfig = System::getContainer()->get('huh.filter.manager')->findById($listConfig->filter))) {
            return [];
        }

        $filter = (object) $filterConfig->getFilter();

        return \Contao\System::getContainer()->get('huh.utils.choice.field')->getCachedChoices([
            'dataContainer' => $filter->dataContainer,
        ]);
    }
}

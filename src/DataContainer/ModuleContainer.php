<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\DataContainer;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\ModuleModel;
use HeimrichHannot\ListBundle\Module\ModuleList;

class ModuleContainer
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Find all list modules. Returned as Array with ID -> Name.
     *
     * Used for example in Reader Bundle
     *
     * @return array
     */
    public function getAllListModules()
    {
        $listModules = [];
        /** @var ModuleModel $adapter */
        $modules = $this->framework->getAdapter(ModuleModel::class)->findBy('type', ModuleList::TYPE);

        if (!$modules) {
            return $listModules;
        }

        foreach ($modules as $module) {
            $listModules[$module->id] = $module->name;
        }

        return $listModules;
    }
}

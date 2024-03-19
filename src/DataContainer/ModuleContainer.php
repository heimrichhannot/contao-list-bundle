<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\Image;
use Contao\ModuleModel;
use Contao\StringUtil;
use HeimrichHannot\ListBundle\Controller\FrontendModule\ListFrontendModuleController;

class ModuleContainer
{
    private ContaoFramework $framework;

    public function __construct(ContaoFramework $framework)
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
    public function getAllListModules(): array
    {
        $listModules = [];
        /** @var ModuleModel $adapter */
        $modules = $this->framework->getAdapter(ModuleModel::class)->findBy('type', ListFrontendModuleController::TYPE);

        if (!$modules) {
            return $listModules;
        }

        foreach ($modules as $module) {
            $listModules[$module->id] = $module->name;
        }

        return $listModules;
    }

    /**
     * Return the list config wizard.
     *
     * @return string
     */
    public function editListConfigurationWizard(DataContainer $dc): string
    {
        $this->framework->getAdapter(Controller::class)->loadLanguageFile('tl_list_config');
        /** @var Image $image */
        $image = $this->framework->getAdapter(Image::class);

        return ($dc->value < 1) ? '' : ' <a href="contao?do=list_configs&amp;act=edit&amp;id='.$dc->value.'&amp;popup=1&amp;nb=1&amp;rt='.REQUEST_TOKEN.'" title="'.sprintf(StringUtil::specialchars($GLOBALS['TL_LANG']['tl_list_config']['edit'][1]), $dc->value).'" onclick="Backend.openModalIframe({\'title\':\''.StringUtil::specialchars(str_replace("'", "\\'", sprintf($GLOBALS['TL_LANG']['tl_list_config']['edit'][1], $dc->value))).'\',\'url\':this.href});return false">'.$image->getHtml('alias.svg', $GLOBALS['TL_LANG']['tl_list_config']['edit'][0]).'</a>';
    }
}

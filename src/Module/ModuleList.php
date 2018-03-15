<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Module;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Module;
use Contao\ModuleModel;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Manager\ListManagerInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use Patchwork\Utf8;

class ModuleList extends Module
{
    protected $strTemplate = 'mod_list';

    /**
     * @var ContaoFramework
     */
    protected $framework;

    /**
     * @var ListManagerInterface
     */
    protected $manager;

    /**
     * @var ListConfigModel
     */
    protected $listConfig;

    /**
     * @var FilterConfig
     */
    protected $filterConfig;

    /**
     * @var ListConfigRegistry
     */
    protected $listConfigRegistry;

    /**
     * @var object
     */
    protected $filter;

    /**
     * @var Request
     */
    protected $request;

    /**
     * ModuleList constructor.
     *
     * @param ModuleModel $objModule
     * @param string      $strColumn
     */
    public function __construct(ModuleModel $objModule, $strColumn = 'main')
    {
        $this->framework = System::getContainer()->get('contao.framework');

        parent::__construct($objModule, $strColumn);

        Controller::loadDataContainer('tl_list_config');
        System::loadLanguageFile('tl_list_config');

        $this->listConfigRegistry = System::getContainer()->get('huh.list.list-config-registry');
        $this->filterRegistry = System::getContainer()->get('huh.filter.registry');
        $this->request = System::getContainer()->get('huh.request');

        // retrieve list config
        $this->listConfig = $this->getListConfig((int) $objModule->listConfig);

        $this->manager = $this->getListManagerByName($this->listConfig->manager ?: 'default');
        $this->manager->setModuleData($this->arrData);

        $this->filterConfig = $this->manager->getFilterConfig();
        $this->filter = (object) $this->filterConfig->getFilter();
    }

    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0]).' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        if (null === $this->manager) {
            return parent::generate();
        }

        $this->framework->getAdapter(Controller::class)->loadDataContainer('tl_list_config');
        $this->framework->getAdapter(Controller::class)->loadDataContainer($this->filter->dataContainer);
        $this->framework->getAdapter(System::class)->loadLanguageFile($this->filter->dataContainer);

        if (null !== ($listClass = $this->manager->getListByName($this->listConfig->list ?: 'default'))) {
            $reflection = new \ReflectionClass($listClass);

            if (!$reflection->implementsInterface(ListInterface::class)) {
                throw new \Exception(sprintf('Item class %s must implement %s', $listClass, ListInterface::class));
            }

            if (!$reflection->implementsInterface(\JsonSerializable::class)) {
                throw new \Exception(sprintf('Item class %s must implement %s', $listClass, \JsonSerializable::class));
            }

            $this->manager->setList(new $listClass($this->manager));
        }

        return parent::generate();
    }

    public function getListConfig(int $listConfigId): ListConfigModel
    {
        if (!$listConfigId || null === ($listConfig = $this->listConfigRegistry->findByPk($listConfigId))) {
            throw new \Exception(sprintf('The module %s has no valid list config. Please set one.', $this->moduleData['id']));
        }

        // compute list config respecting the inheritance hierarchy
        $listConfig = $this->listConfigRegistry->computeListConfig(
            $listConfigId
        );

        return $listConfig;
    }

    protected function compile()
    {
        $this->manager->getList()->handleShare();

        // apply module fields to template
        $this->Template->headline = $this->headline;
        $this->Template->hl = $this->hl;

        // add class to every list template
        $cssID = $this->cssID;
        $cssID[1] = $cssID[1].($cssID[1] ? ' ' : '').'huh-list';

        $this->cssID = $cssID;

        $this->Template->list = function (string $listTemplate = null, string $itemTemplate = null, array $data = []) {
            return $this->manager->getList()->parse($listTemplate, $itemTemplate, $data);
        };
    }

    /**
     * Get the list manager.
     *
     * @param string $name
     *
     * @throws \Exception
     *
     * @return null|ListManagerInterface
     */
    protected function getListManagerByName(string $name): ?ListManagerInterface
    {
        $config = System::getContainer()->getParameter('huh.list');

        if (!isset($config['list']['managers'])) {
            return null;
        }

        $managers = $config['list']['managers'];

        foreach ($managers as $manager) {
            if ($manager['name'] == $name) {
                if (!System::getContainer()->has($manager['id'])) {
                    return null;
                }

                /** @var ListManagerInterface $manager */
                $manager = System::getContainer()->get($manager['id']);
                $interfaces = class_implements($manager);

                if (!is_array($interfaces) || !in_array(ListManagerInterface::class, $interfaces, true)) {
                    throw new \Exception(sprintf('List manager service %s must implement %s', $manager['id'], ListManagerInterface::class));
                }

                return $manager;
            }
        }

        return null;
    }
}

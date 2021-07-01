<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Module;

use Contao\BackendTemplate;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Module;
use Contao\ModuleModel;
use Contao\System;
use Contao\Template;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\ListBundle\Asset\FrontendAsset;
use HeimrichHannot\ListBundle\Event\ListCompileEvent;
use HeimrichHannot\ListBundle\Exception\InterfaceNotImplementedException;
use HeimrichHannot\ListBundle\Exception\InvalidListConfigException;
use HeimrichHannot\ListBundle\Exception\InvalidListManagerException;
use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Manager\ListManagerInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;
use Patchwork\Utf8;

class ModuleList extends Module
{
    const TYPE = 'huhlist';

    protected $strTemplate = 'mod_list';

    /**
     * @var ContaoFramework
     */
    protected $framework;

    /**
     * @var ListManagerInterface
     */
    protected $listManager;

    /**
     * @var ListConfigModel
     */
    protected $listConfig;

    /**
     * @var FilterConfig
     */
    protected $filterConfig;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var ListConfigRegistry
     */
    protected $listConfigRegistry;

    /**
     * @var object
     */
    protected $filter;

    /**
     * @var FrontendAsset
     */
    protected $frontendAsset;

    /**
     * ModuleList constructor.
     *
     * @param string $strColumn
     *
     * @throws InvalidListManagerException
     * @throws InvalidListConfigException
     *
     * @codeCoverageIgnore
     */
    public function __construct(ModuleModel $objModule, $strColumn = 'main')
    {
        $framework = System::getContainer()->get('contao.framework');

        parent::__construct($objModule, $strColumn);

        if (System::getContainer()->get('huh.utils.container')->isBackend()) {
            return;
        }

        Controller::loadDataContainer('tl_list_config');
        System::loadLanguageFile('tl_list_config');

        $listConfigRegistry = System::getContainer()->get('huh.list.list-config-registry');
        $filterManager = System::getContainer()->get('huh.filter.manager');

        // retrieve list config
        $listConfig = System::getContainer()->get('huh.list.list-config-registry')->getComputedListConfig((int) $objModule->listConfig);

        $manager = System::getContainer()->get('huh.list.util.manager')->getListManagerByName($listConfig->manager ?: 'default');
        $manager->setListConfig($listConfig);
        $manager->setModuleData($this->arrData);

        $filterConfig = $manager->getFilterConfig();

        $frontendAsset = System::getContainer()->get(FrontendAsset::class);

        $this->initModule($objModule, $framework, $listConfigRegistry, $filterManager, $manager, $listConfig, $filterConfig, $frontendAsset);
    }

    /**
     * Testable init method.
     */
    public function initModule(ModuleModel $model, ContaoFrameworkInterface $framework, ListConfigRegistry $listConfigRegistry, FilterManager $filterManager, ListManagerInterface $listManager, ListConfigModel $listConfigModel, FilterConfig $filterConfig, FrontendAsset $frontendAsset)
    {
        if (!$this->objModel) {
            $this->objModel = $model;
        }
        $this->framework = $framework;
        $this->listConfigRegistry = $listConfigRegistry;
        $this->filterManager = $filterManager;
        $this->listManager = $listManager;
        $this->listConfig = $listConfigModel;
        $this->filterConfig = $filterConfig;
        $this->filter = (object) $filterConfig->getFilter();
        $this->frontendAsset = $frontendAsset;
    }

    /**
     * @throws \Exception
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function generate()
    {
        if (System::getContainer()->get('huh.utils.container')->isBackend()) {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0]).' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        if (null === $this->listManager) {
            return parent::generate();
        }

        if (!$this->doGenerate()) {
            return '';
        }

        $this->frontendAsset->addFrontendAssets();

        return parent::generate();
    }

    /**
     * Testable generate function.
     *
     * @throws InterfaceNotImplementedException
     * @throws \ReflectionException
     *
     * @return bool
     */
    public function doGenerate()
    {
        $this->framework->getAdapter(Controller::class)->loadDataContainer('tl_list_config');
        $this->framework->getAdapter(Controller::class)->loadDataContainer($this->filter->dataContainer);
        $this->framework->getAdapter(Controller::class)->loadLanguageFile($this->filter->dataContainer);

        if (null !== ($listClass = $this->listManager->getListByName($this->listConfig->list ?: 'default'))) {
            $reflection = new \ReflectionClass($listClass);

            if (!$reflection->implementsInterface(ListInterface::class)) {
                throw new InterfaceNotImplementedException(ListInterface::class, $listClass);
            }

            if (!$reflection->implementsInterface(\JsonSerializable::class)) {
                throw new InterfaceNotImplementedException(\JsonSerializable::class, $listClass);
            }

            $this->listManager->setList(new $listClass($this->listManager));
        }

        if (true === (bool) $this->listManager->getListConfig()->doNotRenderEmpty
            && empty($this->listManager->getList()->getItems())) {
            /** @var FilterQueryBuilder $queryBuilder */
            $queryBuilder = $this->listManager->getFilterManager()->getQueryBuilder($this->filter->id);
            $fields = $this->filter->dataContainer.'.* ';

            if ($totalCount = $queryBuilder->select($fields)->execute()->rowCount() < 1) {
                return false;
            }
        }

        return true;
    }

    public function getFilterConfig(): FilterConfig
    {
        return $this->filterConfig;
    }

    /**
     * @deprecated Use getListManager instead
     */
    public function getManager(): ListManagerInterface
    {
        return $this->listManager;
    }

    public function getListManager(): ListManagerInterface
    {
        return $this->listManager;
    }

    public function doCompile(Template $template, array $css)
    {
        $this->listManager->getList()->handleShare();

        // apply module fields to template
        $template->headline = $this->headline;
        $template->hl = $this->hl;

        // add class to every list template
        $cssID = $css;
        $cssID[1] = $cssID[1].($cssID[1] ? ' ' : '').'huh-list '.$this->listManager->getList()->getDataContainer();

        $css = $cssID;

        $template->noSearch = (bool) $this->listManager->getListConfig()->noSearch;

        $template->list = function (string $listTemplate = null, string $itemTemplate = null, array $data = []) {
            return $this->listManager->getList()->parse($listTemplate, $itemTemplate, $data);
        };

        return $css;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function compile()
    {
        $css = $this->doCompile($this->Template, $this->cssID);
        $this->cssID = $css;
        System::getContainer()->get('event_dispatcher')->dispatch(
            ListCompileEvent::NAME,
            new ListCompileEvent($this->Template, $this, $this->listManager->getListConfig())
        );
    }
}

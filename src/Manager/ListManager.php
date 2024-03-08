<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Exception;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\FilterBundle\Util\TwigSupportPolyfill\TwigTemplateLocator;
use HeimrichHannot\ListBundle\Backend\ListConfig;
use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\ListBundle\Pagination\RandomPagination;
use HeimrichHannot\ListBundle\Registry\ListConfigElementRegistry;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment as TwigEnvironment;

class ListManager implements ListManagerInterface
{
    protected ContaoFramework $framework;
    protected ListConfigModel $listConfig;
    protected ListConfigRegistry $listConfigRegistry;
    protected ListConfigElementRegistry $listConfigElementRegistry;
    protected FilterManager $filterManager;
    protected RequestStack $requestStack;
    protected Utils $utils;
    protected TwigEnvironment $twig;
    protected TwigTemplateLocator $templateLocator;

    protected Database $database;

    /**
     * @var ListInterface
     */
    protected $list;
    /**
     * @var DataContainer
     */
    protected $dc;
    /**
     * @var array
     */
    protected $moduleData;
    protected static array $listConfigCache = [];

    public function __construct(
        ContaoFramework           $framework,
        ListConfigRegistry        $listConfigRegistry,
        ListConfigElementRegistry $listConfigElementRegistry,
        FilterManager             $filterManager,
        RequestStack              $requestStack,
        Utils                     $utils,
        TwigEnvironment           $twig,
        TwigTemplateLocator       $templateLocator
    ) {
        $this->framework = $framework;
        $this->listConfigRegistry = $listConfigRegistry;
        $this->listConfigElementRegistry = $listConfigElementRegistry;
        $this->filterManager = $filterManager;
        $this->requestStack = $requestStack;
        $this->utils = $utils;
        $this->twig = $twig;
        $this->database = $framework->createInstance(Database::class);
        $this->templateLocator = $templateLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function getListConfig(): ?ListConfigModel
    {
        $listConfigId = $this->moduleData['listConfig'];

        // Caching
        if (isset(static::$listConfigCache[$listConfigId]) && null !== static::$listConfigCache[$listConfigId]) {
            $this->listConfig = static::$listConfigCache[$listConfigId];

            return $this->listConfig;
        }

        if (!$listConfigId || null === ($listConfig = $this->listConfigRegistry->findByPk($listConfigId))) {
            if ($this->utils->container()->isBackend()) {
                return null;
            }

            throw new Exception(sprintf('The module %s has no valid list config. Please set one.', $this->moduleData['id']));
        }

        // compute list config respecting the inheritance hierarchy
        $listConfig = $this->listConfigRegistry->computeListConfig($listConfigId);

        $this->listConfig = $listConfig;

        static::$listConfigCache[$listConfigId] = $this->listConfig;

        return $listConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function setListConfig(ListConfigModel $listConfig): void
    {
        $this->moduleData['listConfig'] = $listConfig->id;
        $this->listConfig = $listConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function setModuleData(array $moduleData): void
    {
        $this->moduleData = $moduleData;
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleData(): array
    {
        return $this->moduleData;
    }

    /**
     * {@inheritdoc}
     */
    public function setList(ListInterface $list): void
    {
        $this->list = $list;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(): ListInterface
    {
        return $this->list;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemClassByName(string $name): ?string
    {
        $config = System::getContainer()->getParameter('huh.list');

        if (!isset($config['list']['items'])) {
            return null;
        }

        $items = $config['list']['items'];

        foreach ($items as $item) {
            if ($item['name'] == $name) {
                return class_exists($item['class']) ? $item['class'] : null;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemTemplateByName(string $name)
    {
        $config = System::getContainer()->getParameter('huh.list');

        if (!isset($config['list']['templates']['item'])) {
            return $this->templateLocator->getTemplatePath($name);
        }

        $templates = $config['list']['templates']['item'];

        foreach ($templates as $template) {
            if ($template['name'] == $name) {
                return $template['template'];
            }
        }

        return $this->templateLocator->getTemplatePath($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemChoiceTemplateByName(string $name)
    {
        $config = System::getContainer()->getParameter('huh.list');

        if (!isset($config['list']['templates']['item_choice'])) {
            return $this->templateLocator->getTemplatePath($name);
        }

        $templates = $config['list']['templates']['item_choice'];

        foreach ($templates as $template) {
            if ($template['name'] == $name) {
                return $template['template'];
            }
        }

        return $this->templateLocator->getTemplatePath($name);
    }

    /**
     * Get the list.
     *
     * @throws Exception
     *
     * @return ListInterface|null
     */
    public function getListByName(string $name): ?string
    {
        $config = System::getContainer()->getParameter('huh.list');

        if (!isset($config['list']['lists'])) {
            return null;
        }

        $items = $config['list']['lists'];

        foreach ($items as $item) {
            if ($item['name'] == $name) {
                return class_exists($item['class']) ? $item['class'] : null;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getListTemplateByName(string $name)
    {
        $config = System::getContainer()->getParameter('huh.list');

        if (!isset($config['list']['templates']['list'])) {
            return $this->templateLocator->getTemplatePath($name);
        }

        $templates = $config['list']['templates']['list'];

        foreach ($templates as $template) {
            if ($template['name'] == $name) {
                return $template['template'];
            }
        }

        return $this->templateLocator->getTemplatePath($name);
    }

    public function getCurrentSorting(): array
    {
        $listConfig = $this->getListConfig();
        $filter = (object) $this->getFilterConfig()->getFilter();
        $request = $this->getRequestStack()->getCurrentRequest();
        $sortingAllowed = $listConfig->isTableList && $listConfig->hasHeader && $listConfig->sortingHeader;

        $currentSorting = [];

        // GET parameter
        if ($sortingAllowed && ($orderField = Input::get('order')) && ($sort = Input::get('sort'))) {
            // anti sql injection: check if field exists
            /** @var Database $db */
            $db = $this->getFramework()->getAdapter(Database::class);

            if ($db->getInstance()->fieldExists($orderField, $filter->dataContainer)
                && \in_array($sort, ListConfig::SORTING_DIRECTIONS)) {
                $currentSorting = [
                    'order' => Input::get('order'),
                    'sort' => Input::get('sort'),
                ];
            }
        } // initial
        else
        {
            $currentSorting = match ($listConfig->sortingMode)
            {
                ListConfig::SORTING_MODE_TEXT => ['order' => $listConfig->sortingText],
                ListConfig::SORTING_MODE_RANDOM => ['order' => ListConfig::SORTING_MODE_RANDOM],
                ListConfig::SORTING_MODE_MANUAL => ['order' => ListConfig::SORTING_MODE_MANUAL],
                default => [
                    'order' => $filter->dataContainer . '.' . $listConfig->sortingField,
                    'sort' => $listConfig->sortingDirection,
                ],
            };
        }

        return $currentSorting;
    }

    /**
     * @throws Exception
     */
    public function applyListConfigSortingToQueryBuilder(FilterQueryBuilder $queryBuilder): void
    {
        $listConfig = $this->getListConfig();
        $filter = (object) $this->getFilterConfig();

        $currentSorting = $this->getCurrentSorting();

        if (ListConfig::SORTING_MODE_RANDOM == $currentSorting['order']) {
            $randomSeed = Input::get(RandomPagination::PARAM_RANDOM) ?: rand(1, 500);
            $queryBuilder->orderBy('RAND("'.(int) $randomSeed.'")');
        } elseif (ListConfig::SORTING_MODE_MANUAL == $currentSorting['order']) {
            $sortingItems = StringUtil::deserialize($listConfig->sortingItems, true);

            if (!empty($sortingItems)) {
                $queryBuilder->orderBy('FIELD('.$filter->dataContainer.'.id,'.implode(',', $sortingItems).')', ' ');
            }
        } else {
            if (!empty($currentSorting)) {
                $queryBuilder->orderBy($currentSorting['order'], $currentSorting['sort']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getListConfigElementRegistry(): ListConfigElementRegistry
    {
        return $this->listConfigElementRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getTwig(): TwigEnvironment
    {
        return $this->twig;
    }

    /**
     * {@inheritdoc}
     */
    public function getFramework(): ContaoFramework
    {
        return $this->framework;
    }

    public function getFilterConfig(): FilterConfig
    {
        $filterId = $this->getListConfig()->filter;

        if (!$filterId || null === ($filterConfig = System::getContainer()->get('huh.filter.manager')->findById($filterId))) {
            throw new Exception(sprintf('The module %s has no valid filter. Please set one.', $this->moduleData['id']));
        }

        return $filterConfig;
    }

    public function getRequestStack(): RequestStack
    {
        return $this->requestStack;
    }

    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFilterManager(): FilterManager
    {
        return $this->filterManager;
    }

    public function setFilterManager(FilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }
}
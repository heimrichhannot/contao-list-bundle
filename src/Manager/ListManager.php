<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\ListBundle\Backend\ListConfig;
use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\ListBundle\Pagination\RandomPagination;
use HeimrichHannot\ListBundle\Registry\ListConfigElementRegistry;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Form\FormUtil;
use HeimrichHannot\UtilsBundle\Image\ImageUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use Twig\Environment;

class ListManager implements ListManagerInterface
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var ListConfigModel
     */
    protected $listConfig;

    /**
     * @var ListConfigRegistry
     */
    protected $listConfigRegistry;

    /**
     * @var ListConfigElementRegistry
     */
    protected $listConfigElementRegistry;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ModelUtil
     */
    protected $modelUtil;

    /**
     * @var UrlUtil
     */
    protected $urlUtil;

    /**
     * @var FormUtil
     */
    protected $formUtil;

    /**
     * @var ContainerUtil
     */
    protected $containerUtil;

    /**
     * @var Environment
     */
    protected $twig;

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

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var array
     */
    protected static $listConfigCache = [];
    /**
     * @var TwigTemplateLocator
     */
    protected $templateLocator;

    public function __construct(
        ContaoFrameworkInterface $framework,
        ListConfigRegistry $listConfigRegistry,
        ListConfigElementRegistry $listConfigElementRegistry,
        FilterManager $filterManager,
        Request $request,
        ModelUtil $modelUtil,
        UrlUtil $urlUtil,
        ContainerUtil $containerUtil,
        ImageUtil $imageUtil,
        FormUtil $formUtil,
        Environment $twig,
        TwigTemplateLocator $templateLocator
    ) {
        $this->framework = $framework;
        $this->listConfigRegistry = $listConfigRegistry;
        $this->listConfigElementRegistry = $listConfigElementRegistry;
        $this->filterManager = $filterManager;
        $this->request = $request;
        $this->modelUtil = $modelUtil;
        $this->urlUtil = $urlUtil;
        $this->formUtil = $formUtil;
        $this->containerUtil = $containerUtil;
        $this->imageUtil = $imageUtil;
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
            if (System::getContainer()->get('huh.utils.container')->isBackend()) {
                return null;
            }

            throw new \Exception(sprintf('The module %s has no valid list config. Please set one.', $this->moduleData['id']));
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
    public function getItemClassByName(string $name)
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
     * @throws \Exception
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
        $request = $this->getRequest();
        $sortingAllowed = $listConfig->isTableList && $listConfig->hasHeader && $listConfig->sortingHeader;

        // GET parameter
        if ($sortingAllowed && ($orderField = $request->getGet('order')) && ($sort = $request->getGet('sort'))) {
            // anti sql injection: check if field exists
            /** @var Database $db */
            $db = $this->getFramework()->getAdapter(Database::class);

            if ($db->getInstance()->fieldExists($orderField, $filter->dataContainer)
                && \in_array($sort, ListConfig::SORTING_DIRECTIONS)) {
                $currentSorting = [
                    'order' => $request->getGet('order'),
                    'sort' => $request->getGet('sort'),
                ];
            } else {
                $currentSorting = [];
            }
        } // initial
        else {
            switch ($listConfig->sortingMode) {
                case ListConfig::SORTING_MODE_TEXT:
                    $currentSorting = [
                        'order' => $listConfig->sortingText,
                    ];

                    break;

                case ListConfig::SORTING_MODE_RANDOM:
                    $currentSorting = [
                        'order' => ListConfig::SORTING_MODE_RANDOM,
                    ];

                    break;

                case ListConfig::SORTING_MODE_MANUAL:
                    $currentSorting = [
                        'order' => ListConfig::SORTING_MODE_MANUAL,
                    ];

                    break;

                default:
                    $currentSorting = [
                        'order' => $filter->dataContainer.'.'.$listConfig->sortingField,
                        'sort' => $listConfig->sortingDirection,
                    ];

                    break;
            }
        }

        return $currentSorting;
    }

    public function applyListConfigSortingToQueryBuilder(FilterQueryBuilder $queryBuilder)
    {
        $listConfig = $this->getListConfig();
        $filter = (object) $this->getFilterConfig();

        $currentSorting = $this->getCurrentSorting();

        if (ListConfig::SORTING_MODE_RANDOM == $currentSorting['order']) {
            $randomSeed = $this->getRequest()->getGet(RandomPagination::PARAM_RANDOM) ?: rand(1, 500);
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
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * {@inheritdoc}
     */
    public function getFramework(): ContaoFrameworkInterface
    {
        return $this->framework;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormUtil(): FormUtil
    {
        return $this->formUtil;
    }

    public function getFilterConfig(): FilterConfig
    {
        $filterId = $this->getListConfig()->filter;

        if (!$filterId || null === ($filterConfig = System::getContainer()->get('huh.filter.manager')->findById($filterId))) {
            throw new \Exception(sprintf('The module %s has no valid filter. Please set one.', $this->moduleData['id']));
        }

        return $filterConfig;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
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

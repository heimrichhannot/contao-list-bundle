<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\ListBundle\Registry\ListConfigElementRegistry;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Form\FormUtil;
use HeimrichHannot\UtilsBundle\Image\ImageUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;

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
     * @var \Twig_Environment
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
        \Twig_Environment $twig
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
    }

    /**
     * {@inheritdoc}
     */
    public function getListConfig(): ListConfigModel
    {
        $listConfigId = $this->moduleData['listConfig'];

        // Caching
        if (isset(static::$listConfigCache[$listConfigId]) && null !== static::$listConfigCache[$listConfigId]) {
            $this->listConfig = static::$listConfigCache[$listConfigId];

            return $this->listConfig;
        }

        if (!$listConfigId || null === ($listConfig = $this->listConfigRegistry->findByPk($listConfigId))) {
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
            return System::getContainer()->get('huh.utils.template')->getTemplate($name);
        }

        $templates = $config['list']['templates']['item'];

        foreach ($templates as $template) {
            if ($template['name'] == $name) {
                return $template['template'];
            }
        }

        return System::getContainer()->get('huh.utils.template')->getTemplate($name);
    }

    /**
     * Get the list.
     *
     * @param string $name
     *
     * @throws \Exception
     *
     * @return null|ListInterface
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
            return System::getContainer()->get('huh.utils.template')->getTemplate($name);
        }

        $templates = $config['list']['templates']['list'];

        foreach ($templates as $template) {
            if ($template['name'] == $name) {
                return $template['template'];
            }
        }

        return System::getContainer()->get('huh.utils.template')->getTemplate($name);
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
    public function getTwig(): \Twig_Environment
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

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return FilterManager
     */
    public function getFilterManager(): FilterManager
    {
        return $this->filterManager;
    }

    /**
     * @param FilterManager $filterManager
     */
    public function setFilterManager(FilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }
}

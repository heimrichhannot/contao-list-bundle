<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\ListBundle\Registry\ListConfigElementRegistry;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Form\FormUtil;

interface ListManagerInterface
{
    /**
     * Set the current module data.
     *
     * @param array $moduleData
     */
    public function setModuleData(array $moduleData): void;

    /**
     * Get current module data.
     *
     * @return array
     */
    public function getModuleData(): array;

    /**
     * Get the list config model.
     *
     * @return ListConfigModel|null
     */
    public function getListConfig(): ?ListConfigModel;

    /**
     * Get the filter config.
     *
     * @return FilterConfig
     */
    public function getFilterConfig(): FilterConfig;

    /**
     * Set the current list config model.
     *
     * @return mixed
     */
    public function setListConfig(ListConfigModel $listConfig): void;

    /**
     * Set the current list.
     *
     * @param ListInterface $list
     */
    public function setList(ListInterface $list): void;

    /**
     * Get the current list.
     *
     * @return ListInterface
     */
    public function getList(): ListInterface;

    /**
     * Get current list config element registry.
     *
     * @return ListConfigElementRegistry
     */
    public function getListConfigElementRegistry(): ListConfigElementRegistry;

    /**
     * Get the current item template path.
     *
     * @param string $name Item template name
     *
     * @return string|null
     */
    public function getItemTemplateByName(string $name);

    /**
     * Get the current item choice template path.
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getItemChoiceTemplateByName(string $name);

    /**
     * Get the list template path.
     *
     * @param string $name List template name
     *
     * @return string|null
     */
    public function getListTemplateByName(string $name);

    /**
     * Gets a list class by a given name.
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getListByName(string $name): ?string;

    /**
     * Get the current item class.
     *
     * @param string $name Item name
     *
     * @return string|null
     */
    public function getItemClassByName(string $name);

    /**
     * Get current twig environment.
     *
     * @return \Twig_Environment
     */
    public function getTwig(): \Twig_Environment;

    /**
     * Get the contao framework.
     *
     * @return ContaoFrameworkInterface
     */
    public function getFramework(): ContaoFrameworkInterface;

    /**
     * Get current form utils.
     *
     * @return FormUtil
     */
    public function getFormUtil(): FormUtil;

    /**
     * Get the request service.
     *
     * @return mixed
     */
    public function getRequest();

    /**
     * Sets a request service.
     *
     * @param Request $request
     */
    public function setRequest(Request $request);

    /**
     * @return FilterManager
     */
    public function getFilterManager(): FilterManager;

    /**
     * @param FilterManager $filterRegistry
     */
    public function setFilterManager(FilterManager $filterRegistry);
}

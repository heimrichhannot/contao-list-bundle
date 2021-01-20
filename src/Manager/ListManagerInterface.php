<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
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
     */
    public function setModuleData(array $moduleData): void;

    /**
     * Get current module data.
     */
    public function getModuleData(): array;

    /**
     * Get the list config model.
     */
    public function getListConfig(): ?ListConfigModel;

    /**
     * Get the filter config.
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
     */
    public function setList(ListInterface $list): void;

    /**
     * Get the current list.
     */
    public function getList(): ListInterface;

    /**
     * Get current list config element registry.
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
     */
    public function getTwig(): \Twig_Environment;

    /**
     * Get the contao framework.
     */
    public function getFramework(): ContaoFrameworkInterface;

    /**
     * Get current form utils.
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
     */
    public function setRequest(Request $request);

    public function getFilterManager(): FilterManager;

    public function setFilterManager(FilterManager $filterRegistry);
}

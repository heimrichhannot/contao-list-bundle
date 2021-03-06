<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Registry;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use HeimrichHannot\ConfigElementTypeBundle\ConfigElementType\ConfigElementTypeInterface;
use HeimrichHannot\ListBundle\ConfigElementType\ListConfigElementTypeInterface;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;

class ListConfigElementRegistry
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var ConfigElementTypeInterface[]|ListConfigElementTypeInterface[]|array
     */
    protected $configElementTypes = [];

    /**
     * Constructor.
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Add a list config element type to the registry.
     *
     * @param ConfigElementTypeInterface|ListConfigElementTypeInterface $listConfigElementType
     */
    public function addListConfigElementType($listConfigElementType): void
    {
        $this->configElementTypes[$listConfigElementType::getType()] = $listConfigElementType;
    }

    /**
     * Get a list config element type from the registry.
     *
     * @return ConfigElementTypeInterface|ListConfigElementTypeInterface|mixed|null
     */
    public function getListConfigElementType(string $type)
    {
        return isset($this->configElementTypes[$type]) ? $this->configElementTypes[$type] : null;
    }

    /**
     * @return array|ListConfigElementTypeInterface[]|ConfigElementTypeInterface[]
     */
    public function getConfigElementTypes()
    {
        return $this->configElementTypes;
    }

    /**
     * Adapter function for the model's findBy method.
     *
     * @param mixed $column
     * @param mixed $value
     *
     * @return \Contao\Model\Collection|ListConfigElementModel|null
     */
    public function findBy($column, $value, array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
            'tl_list_config_element', $column, $value, $options);
    }

    /**
     * Adapter function for the model's findOneBy method.
     *
     * @param mixed $column
     * @param mixed $value
     *
     * @return ListConfigElementModel|null
     */
    public function findOneBy($column, $value, array $options = [])
    {
        $options = array_merge(
            ['limit' => 1, 'return' => 'Model'],
            $options
        );

        return System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
            'tl_list_config_element', $column, $value, $options);
    }

    /**
     * Adapter function for the model's findByPk method.
     *
     * @param mixed $column
     * @param mixed $value
     *
     * @return \Contao\Model\Collection|ListConfigElementModel|null
     */
    public function findByPk($pk, array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findModelInstanceByPk(
            'tl_list_config_element', $pk, $options);
    }

    /**
     * Returns the filter associated to a list config element.
     *
     * @param int $listConfigPk
     *
     * @return array|null
     */
    public function getFilterByPk(int $listConfigElementPk)
    {
        if (null === ($listConfigElement = $this->findByPk($listConfigElementPk))) {
            return null;
        }

        return System::getContainer()->get('huh.list.list-config-registry')->getFilterByPk($listConfigElement->pid);
    }

    /**
     * Get the type class by given element name.
     *
     * @param $name
     *
     * @return string|null
     *
     * @deprecated Will be removed next major version
     */
    public function getElementClassByName($name)
    {
        $config = System::getContainer()->getParameter('huh.list');
        $templates = $config['list']['config_element_types'];

        foreach ($templates as $template) {
            if ($template['name'] == $name) {
                return class_exists($template['class']) ? $template['class'] : null;
            }
        }

        return null;
    }
}

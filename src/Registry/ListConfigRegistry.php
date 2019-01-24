<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Registry;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use HeimrichHannot\ListBundle\Exception\InvalidListConfigException;
use HeimrichHannot\ListBundle\Model\ListConfigModel;

class ListConfigRegistry
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Adapter function for the model's findBy method.
     *
     * @param array $options
     *
     * @return \Contao\Model\Collection|ListConfigModel|null
     */
    public function findAll(array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findAllModelInstances(
            'tl_list_config',
            $options
        );
    }

    /**
     * Adapter function for the model's findBy method.
     *
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return \Contao\Model\Collection|ListConfigModel|null
     */
    public function findBy($column, $value, array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
            'tl_list_config',
            $column,
            $value,
            $options
        );
    }

    /**
     * Adapter function for the model's findOneBy method.
     *
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return \Contao\Model\Collection|ListConfigModel|null
     */
    public function findOneBy($column, $value, array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
            'tl_list_config',
            $column,
            $value,
            $options
        );
    }

    /**
     * Adapter function for the model's findByPk method.
     *
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return ListConfigModel|null
     */
    public function findByPk($pk, array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findModelInstanceByPk(
            'tl_list_config',
            $pk,
            $options
        );
    }

    /**
     * Returns the filter associated to a list config.
     *
     * @param int $listConfigPk
     *
     * @return array|null
     */
    public function getFilterByPk(int $listConfigPk)
    {
        if (null === ($listConfig = $this->findByPk($listConfigPk))) {
            return null;
        }

        if (!$listConfig->filter || null === ($filterConfig = System::getContainer()->get('huh.filter.manager')->findById($listConfig->filter))) {
            return null;
        }

        return $filterConfig->getFilter();
    }

    public function getOverridableProperty($property, int $listConfigPk)
    {
        if (null === ($listConfig = $this->findByPk($listConfigPk))) {
            return null;
        }

        $parentListConfigs = System::getContainer()->get('huh.utils.model')->findParentsRecursively(
            'parentListConfig',
            'tl_list_config',
            $listConfig
        );

        if (empty($parentListConfigs)) {
            return null;
        }

        return System::getContainer()->get('huh.utils.dca')->getOverridableProperty(
            $property,
            $parentListConfigs
        );
    }

    /**
     * Computes the list config respecting the list config hierarchy (sub list configs can override parts of their ancestors).
     *
     * @param int $listConfigPk
     *
     * @return ListConfigModel|null
     */
    public function computeListConfig(int $listConfigPk)
    {
        if (null === ($listConfig = $this->findByPk($listConfigPk))) {
            return null;
        }

        if (!$listConfig->parentListConfig) {
            return $listConfig;
        }

        $computedListConfig = new ListConfigModel();

        $parentListConfigs = System::getContainer()->get('huh.utils.model')->findParentsRecursively(
            'parentListConfig',
            'tl_list_config',
            $listConfig
        );

        $rootListConfig = System::getContainer()->get('huh.utils.model')->findRootParentRecursively(
            'parentListConfig',
            'tl_list_config',
            $listConfig
        );

        Controller::loadDataContainer('tl_list_config');

        foreach ($GLOBALS['TL_DCA']['tl_list_config']['fields'] as $field => $data) {
            if ($data['eval']['notOverridable']) {
                $computedListConfig->{$field} = $rootListConfig->{$field};
            } else {
                $computedListConfig->{$field} = System::getContainer()->get('huh.utils.dca')->getOverridableProperty(
                    $field,
                    array_merge($parentListConfigs, [$listConfig])
                );
            }
        }

        return $computedListConfig;
    }

    /**
     * Get computed list config by Id.
     *
     * @param int $listConfigId
     *
     * @return ListConfigModel|null
     */
    public function getComputedListConfig(int $listConfigId): ?ListConfigModel
    {
        if (!$listConfigId || null === ($listConfig = $this->findByPk($listConfigId))) {
            throw new InvalidListConfigException(sprintf('No valid list config given. Please set one.'));
        }

        // compute list config respecting the inheritance hierarchy
        $listConfig = $this->computeListConfig($listConfigId);

        return $listConfig;
    }
}

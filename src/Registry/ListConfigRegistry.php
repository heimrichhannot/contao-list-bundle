<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Registry;

use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model;
use Contao\Model\Collection;
use Contao\System;
use HeimrichHannot\ListBundle\Exception\InvalidListConfigException;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\ListBundle\Util\DCUtil;
use HeimrichHannot\ListBundle\Util\Polyfill;
use HeimrichHannot\UtilsBundle\Util\Utils;

class ListConfigRegistry
{
    protected ContaoFramework $framework;
    protected Utils $utils;

    /**
     * Constructor.
     */
    public function __construct(
        ContaoFramework $framework,
        Utils $utils
    ) {
        $this->framework = $framework;
        $this->utils = $utils;
    }

    /**
     * Adapter function for the model's findBy method.
     *
     * @return Collection<ListConfigModel>|null
     */
    public function findAll(array $options = []): ?Collection
    {
        $table = 'tl_list_config';

        $modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table);
        if (!$modelClass) {
            return null;
        }

        /* @var Adapter<ListConfigModel> $adapter */
        $adapter = $this->framework->getAdapter($modelClass);

        return $adapter->findAll($options);
    }

    /**
     * Adapter function for the model's findBy method.
     *
     * @param mixed $column
     * @param mixed $value
     *
     * @return Collection|ListConfigModel|null
     */
    public function findBy($column, $value, array $options = [])
    {
        return $this->utils->model()->findModelInstancesBy(
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
     *
     * @return Collection|ListConfigModel|null
     */
    public function findOneBy($column, $value, array $options = [])
    {
        return $this->utils->model()->findModelInstancesBy(
            'tl_list_config',
            $column,
            $value,
            $options
        );
    }

    /**
     * Adapter function for the model's findByPk method.
     *
     * @return ListConfigModel|null
     */
    public function findByPk(mixed $pk, array $options = [])
    {
        return ListConfigModel::findByPk($pk, $options);
    }

    /**
     * Returns the filter associated to a list config.
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
        $listConfig = $this->findByPk($listConfigPk);
        if (null === $listConfig) {
            return null;
        }

        $parentListConfigs = $this->utils->model()->findParentsRecursively($listConfig);

        if (empty($parentListConfigs)) {
            return null;
        }

        return DCUtil::getOverridableProperty($property, $parentListConfigs);
    }

    /**
     * Computes the list config respecting the list config hierarchy (sub list configs can override parts of their ancestors).
     *
     * @return ListConfigModel|null
     */
    public function computeListConfig(int $listConfigPk)
    {
        if (null === ($listConfig = $this->findByPk($listConfigPk))) {
            return null;
        }

        $listConfig->rootId = $listConfig->id;

        if (!$listConfig->pid) {
            return $listConfig;
        }

        $computedListConfig = new ListConfigModel();

        $parentListConfigs = $this->utils->model()->findParentsRecursively(
            'pid',
            'tl_list_config',
            $listConfig
        );

        $rootListConfig = Polyfill::findRootParentRecursively(
            $this->utils->model(),
            'pid',
            'tl_list_config',
            $listConfig
        );

        Controller::loadDataContainer('tl_list_config');

        foreach ($GLOBALS['TL_DCA']['tl_list_config']['fields'] as $field => $data) {
            if ($data['eval']['notOverridable'] ?? false) {
                $computedListConfig->{$field} = $rootListConfig->{$field};
            } else {
                $computedListConfig->{$field} = DCUtil::getOverridableProperty(
                    $field,
                    array_merge($parentListConfigs, [$listConfig])
                );
            }
        }

        $computedListConfig->id = $listConfigPk;
        $computedListConfig->rootId = $rootListConfig->id;

        return $computedListConfig;
    }

    /**
     * Get computed list config by Id.
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
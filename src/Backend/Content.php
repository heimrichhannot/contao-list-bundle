<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Backend;

use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\Model;
use Contao\System;
use HeimrichHannot\FilterBundle\Model\FilterPreselectModel;
use HeimrichHannot\ListBundle\Exception\InvalidListConfigException;
use HeimrichHannot\ListBundle\Exception\InvalidListManagerException;
use HeimrichHannot\ListBundle\Item\ItemInterface;

class Content
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Invoke onload_callback.
     *
     * @param DataContainer $dc
     */
    public function onLoad(DataContainer $dc)
    {
        if (null === ($content = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($dc->table, $dc->id))) {
            return;
        }

        $this->toggleFilterPreselect($content, $dc);
    }

    /**
     * Get list of preselect choices.
     *
     * @param DataContainer $dc
     *
     * @throws \Twig_Error_Loader          When the template cannot be found
     * @throws \Twig_Error_Syntax          When an error occurred during compilation
     * @throws \Twig_Error_Runtime         When an error occurred during rendering
     * @throws InvalidListManagerException
     *
     * @return array
     */
    public function getListPreselectChoices(DataContainer $dc): array
    {
        $choices = [];

        if (null === ($content = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($dc->table, $dc->id))) {
            return $choices;
        }

        if ($content->listConfig < 1) {
            return $choices;
        }

        try {
            $listConfig = System::getContainer()->get('huh.list.list-config-registry')->getComputedListConfig((int) $content->listConfig);
        } catch (InvalidListConfigException $e) {
            return $choices;
        }

        try {
            $manager = System::getContainer()->get('huh.list.util.manager')->getListManagerByName($listConfig->manager ?: 'default');
        } catch (InvalidListManagerException $e) {
            return $choices;
        }

        $manager->setListConfig($listConfig);
        $manager->getFilterConfig()->initQueryBuilder();

        $filterConfig = $manager->getFilterConfig();
        $filter = (object) $filterConfig->getFilter();

        $fields = $filter->dataContainer.'.* ';

        $pk = 'id';
        $model = Model::getClassFromTable($filter->dataContainer);

        /** @var Model $model */
        if (class_exists($model)) {
            $pk = $model::getPk();
        }

        /** @var FilterPreselectModel $preselections */
        $data = [];
        $preselections = System::getContainer()->get('contao.framework')->createInstance(FilterPreselectModel::class);

        $queryBuilder = $manager->getFilterConfig()->getQueryBuilder()->resetQueryParts();

        if (null !== ($preselections = $preselections->findPublishedByPidAndTableAndField($content->id, 'tl_content', 'filterPreselect'))) {
            $queryBuilder = System::getContainer()->get('huh.filter.util.filter_preselect')->getPreselectQueryBuilder($filterConfig->getId(), $queryBuilder, $preselections->getModels());
        }

        $items = $queryBuilder->select($fields)->from($filter->dataContainer)->execute()->fetchAll();

        $total = count($items);

        $twig = System::getContainer()->get('twig');

        /*
         * For performance reasons do not use ItemInterface
         */
        foreach ($items as $item) {
            $data = [];
            $data['raw'] = $item;
            $data['total'] = $total;
            $choices[$item[$pk]] = $twig->render($manager->getItemChoiceTemplateByName($listConfig->itemChoiceTemplate ?: 'default'), $data);
        }

        return $choices;
    }

    /**
     * Toggle filterPreselect field on demand.
     *
     * @param ContentModel  $content
     * @param DataContainer $dc
     */
    protected function toggleFilterPreselect(ContentModel $content, DataContainer $dc)
    {
        if ($content->listConfig < 1) {
            return;
        }

        if (null === ($listConfig = System::getContainer()->get('huh.list.list-config-registry')->findByPk($content->listConfig)) || $listConfig->filter < 1) {
            return;
        }

        // update filterConfig from listConfig to maintain tl_filter_preselect requirements
        if ($content->filterConfig !== $listConfig->filter) {
            $content->filterConfig = $listConfig->filter;
            $content->save();
        }

        $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect'] = str_replace('listConfig;', 'listConfig,filterPreselect,listPreselect;', $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect']);
    }
}

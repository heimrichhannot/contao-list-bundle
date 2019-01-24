<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\DataContainer;

use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\Model;
use HeimrichHannot\FilterBundle\Model\FilterPreselectModel;
use HeimrichHannot\FilterBundle\Util\FilterPreselectUtil;
use HeimrichHannot\ListBundle\Exception\InvalidListConfigException;
use HeimrichHannot\ListBundle\Exception\InvalidListManagerException;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;
use HeimrichHannot\ListBundle\Util\ListManagerUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class ContentContainer
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;
    /**
     * @var ModelUtil
     */
    private $modelUtil;
    /**
     * @var ListConfigRegistry
     */
    private $listConfigRegistry;
    /**
     * @var ListManagerUtil
     */
    private $listManagerUtil;
    /**
     * @var FilterPreselectUtil
     */
    private $filterPreselectUtil;
    /**
     * @var \Twig_Environment
     */
    private $twig;

    public function __construct(ContaoFrameworkInterface $framework, ModelUtil $modelUtil, ListConfigRegistry $listConfigRegistry, ListManagerUtil $listManagerUtil, FilterPreselectUtil $filterPreselectUtil, \Twig_Environment $twig)
    {
        $this->framework = $framework;
        $this->modelUtil = $modelUtil;
        $this->listConfigRegistry = $listConfigRegistry;
        $this->listManagerUtil = $listManagerUtil;
        $this->filterPreselectUtil = $filterPreselectUtil;
        $this->twig = $twig;
    }

    /**
     * Invoke onload_callback.
     *
     * @param DataContainer $dc
     */
    public function onLoad(DataContainer $dc)
    {
        if (null === ($content = $this->modelUtil->findModelInstanceByPk($dc->table, $dc->id))) {
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

        /* @var ContentModel */
        if (!$content = $this->modelUtil->findModelInstanceByPk($dc->table, $dc->id)) {
            return $choices;
        }

        if (!$content->listConfig || $content->listConfig < 1) {
            return $choices;
        }

        try {
            $listConfig = $this->listConfigRegistry->getComputedListConfig((int) $content->listConfig);
        } catch (InvalidListConfigException $e) {
            return $choices;
        }

        try {
            $manager = $this->listManagerUtil->getListManagerByName($listConfig->manager ?: 'default');
        } catch (InvalidListManagerException $e) {
            return $choices;
        }

        $manager->setListConfig($listConfig);
        $manager->getFilterConfig()->initQueryBuilder();

        $filterConfig = $manager->getFilterConfig();
        $filter = (object) $filterConfig->getFilter();

        $fields = $filter->dataContainer.'.* ';

        $pk = 'id';
        /** @var Model $model */
        $model = $this->framework->getAdapter(Model::class)->getClassFromTable($filter->dataContainer);

        if (class_exists($model)) {
            $pk = $model::getPk();
        }

        /** @var FilterPreselectModel $preselections */
        $preselections = $this->framework->createInstance(FilterPreselectModel::class);

        $queryBuilder = $manager->getFilterConfig()->getQueryBuilder()->resetQueryParts();

        if ($preselections = $preselections->findPublishedByPidAndTableAndField($content->id, 'tl_content', 'filterPreselect')) {
            $queryBuilder = $this->filterPreselectUtil->getPreselectQueryBuilder($filterConfig->getId(), $queryBuilder, $preselections->getModels());
        }

        $items = $queryBuilder->select($fields)->from($filter->dataContainer)->execute()->fetchAll();

        $total = \count($items);

        /*
         * For performance reasons do not use ItemInterface
         */
        foreach ($items as $item) {
            $data = [];
            $data['raw'] = $item;
            $data['total'] = $total;
            $choices[$item[$pk]] = $this->twig->render($manager->getItemChoiceTemplateByName($listConfig->itemChoiceTemplate ?: 'default'), $data);
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
        if (!$content->listConfig || $content->listConfig < 1) {
            return;
        }

        if (null === ($listConfig = $this->listConfigRegistry->findByPk($content->listConfig)) || !$listConfig->filter || $listConfig->filter < 1) {
            return;
        }

        // update filterConfig from listConfig to maintain tl_filter_preselect requirements
        if ($content->filterConfig !== $listConfig->filter) {
            $content->filterConfig = $listConfig->filter;
            $content->save();
        }

        $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect'] = str_replace(
            'listConfig;',
            'listConfig,filterPreselect,listPreselect;',
            $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect']
        );
        $GLOBALS['TL_DCA']['tl_content']['fields']['filterPreselect']['eval']['submitOnChange'] = true;
    }
}

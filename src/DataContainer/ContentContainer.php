<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\DataContainer;

use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Database;
use Contao\DataContainer;
use Contao\Model;
use Contao\System;
use HeimrichHannot\FilterBundle\Model\FilterPreselectModel;
use HeimrichHannot\FilterBundle\Util\FilterPreselectUtil;
use HeimrichHannot\ListBundle\ContentElement\ContentListPreselect;
use HeimrichHannot\ListBundle\Exception\InvalidListConfigException;
use HeimrichHannot\ListBundle\Exception\InvalidListManagerException;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;
use HeimrichHannot\ListBundle\Util\ListManagerUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

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
     * @var Environment
     */
    private $twig;
    private RequestStack $requestStack;

    public function __construct(ContaoFrameworkInterface $framework, ModelUtil $modelUtil, ListConfigRegistry $listConfigRegistry, ListManagerUtil $listManagerUtil, FilterPreselectUtil $filterPreselectUtil, Environment $twig, RequestStack $requestStack)
    {
        $this->framework = $framework;
        $this->modelUtil = $modelUtil;
        $this->listConfigRegistry = $listConfigRegistry;
        $this->listManagerUtil = $listManagerUtil;
        $this->filterPreselectUtil = $filterPreselectUtil;
        $this->twig = $twig;
        $this->requestStack = $requestStack;
    }

    /**
     * @Callback(table="tl_content", target="config.onload")
     */
    public function onLoad(DataContainer $dc = null): void
    {
        if (null === $dc || !$dc->id || 'edit' !== $this->requestStack->getCurrentRequest()->query->get('act')) {
            return;
        }

        $element = ContentModel::findById($dc->id);

        if (null === $element || ContentListPreselect::TYPE !== $element->type) {
            return;
        }

        $this->toggleFilterPreselect($element, $dc);
    }

    public function getListPreselectListConfigs(DataContainer $dc): array
    {
        $options = [];

        if (null !== ($listConfig = $this->modelUtil->findModelInstancesBy('tl_list_config', ['tl_list_config.hideForListPreselect!=?'], [true]))) {
            while ($listConfig->next()) {
                $options[$listConfig->id] = $listConfig->title;
            }
        }

        asort($options);

        return $options;
    }

    /**
     * @Callback(table="tl_content", target="fields.listPreselect.options")
     */
    public function getListPreselectChoices(DataContainer $dc = null): array
    {
        $choices = [];

        if (null === $dc || !$dc->id) {
            return $choices;
        }

        $contentModel = ContentModel::findById($dc->id);

        if (null === $contentModel) {
            return $choices;
        }

        if (!$contentModel->listConfig || $contentModel->listConfig < 1) {
            return $choices;
        }

        try {
            $listConfig = $this->listConfigRegistry->getComputedListConfig((int) $contentModel->listConfig);
        } catch (InvalidListConfigException $e) {
            return $choices;
        }

        try {
            $manager = $this->listManagerUtil->getListManagerByName($listConfig->manager ?: 'default');
        } catch (InvalidListManagerException $e) {
            return $choices;
        }

        $manager->setListConfig($listConfig);

        // multilingual filter?
        $tmpLang = $GLOBALS['TL_LANGUAGE'];

        if ('tl_article' === $contentModel->ptable) {
            if (null !== ($article = $this->modelUtil->findModelInstanceByPk('tl_article', $contentModel->pid))) {
                if (null !== ($page = $this->modelUtil->findModelInstanceByPk('tl_page', $article->pid))) {
                    $page->loadDetails();

                    if (null !== ($page = $this->modelUtil->findModelInstanceByPk('tl_page', $page->rootId)) &&
                        $page->language !== $GLOBALS['TL_LANGUAGE']) {
                        $GLOBALS['TL_LANGUAGE'] = $page->language;
                    }
                }
            }
        }

        $manager->getFilterConfig()->initQueryBuilder();

        // multilingual filter?
        if ('tl_article' === $contentModel->ptable && $tmpLang !== $GLOBALS['TL_LANGUAGE']) {
            $GLOBALS['TL_LANGUAGE'] = $tmpLang;

            // reload the language files because else the following content elements would've been in the other language
            System::loadLanguageFile('default', $tmpLang, true);
            System::loadLanguageFile('tl_content', $tmpLang, true);
        }

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

        $queryBuilder = $manager->getFilterConfig()->getQueryBuilder();

        if ($preselections = $preselections->findPublishedByPidAndTableAndField($contentModel->id, 'tl_content', 'filterPreselect')) {
            $queryBuilder = $this->filterPreselectUtil->getPreselectQueryBuilder($filterConfig->getId(), $queryBuilder, $preselections->getModels());
        }

        $manager->applyListConfigSortingToQueryBuilder($queryBuilder);

        if ($contentModel->ptable === $filter->dataContainer && $contentModel->pid) {
            $queryBuilder->andWhere($contentModel->ptable.'.id != :preselect_parent_id');
            $queryBuilder->setParameter('preselect_parent_id', $contentModel->pid);
        }

        $items = $queryBuilder->select($fields)->execute()->fetchAll();

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
     */
    protected function toggleFilterPreselect(Model $content)
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

            // handle $blnPreventSaving from Multilingual Model (do not use $content->save())
            $this->framework->createInstance(Database::class)->prepare('UPDATE '.$content::getTable().' %s WHERE '.Database::quoteIdentifier($content::getPk()).'=?')
                ->set(['filterConfig' => $listConfig->filter])
                ->execute($content->{$content::getPk()});
        }

        $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect'] = str_replace(
            'listConfig;',
            'listConfig,filterPreselect,listPreselect;',
            $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect']
        );
        $GLOBALS['TL_DCA']['tl_content']['fields']['filterPreselect']['eval']['submitOnChange'] = true;
    }
}

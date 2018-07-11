<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ContentElement;

use Contao\ContentElement;
use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\FilterBundle\Model\FilterPreselectModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\ListBundle\Event\ListModifyQueryBuilderEvent;
use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Manager\ListManagerInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;

class ContentListPreselect extends ContentElement
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_list_preselect';

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var ListManagerInterface
     */
    protected $manager;

    /**
     * @var ListConfigModel
     */
    protected $listConfig;

    /**
     * @var FilterConfig
     */
    protected $filterConfig;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var ListConfigRegistry
     */
    protected $listConfigRegistry;

    /**
     * @var FilterQueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var object
     */
    protected $filter;

    public function __construct(ContentModel $objElement, string $strColumn = 'main')
    {
        $this->framework = System::getContainer()->get('contao.framework');

        parent::__construct($objElement, $strColumn);

        $this->listConfigRegistry = System::getContainer()->get('huh.list.list-config-registry');
        $this->filterManager = System::getContainer()->get('huh.filter.manager');
        $this->request = System::getContainer()->get('huh.request');

        // retrieve list config
        $this->listConfig = System::getContainer()->get('huh.list.list-config-registry')->getComputedListConfig((int) $objElement->listConfig);

        $this->manager = System::getContainer()->get('huh.list.util.manager')->getListManagerByName($this->listConfig->manager ?: 'default');
        $this->manager->setListConfig($this->listConfig);
        $this->manager->setModuleData($this->arrData);

        $this->filterConfig = $this->manager->getFilterConfig();
        $this->filter = (object) $this->filterConfig->getFilter();
    }

    public function generate()
    {
        if (null === $this->manager) {
            return '';
        }

        $values = array_filter(StringUtil::deserialize($this->objModel->listPreselect, true));

        if (empty($values)) {
            return '';
        }

        $this->preselect();

        $this->framework->getAdapter(Controller::class)->loadDataContainer('tl_list_config');
        $this->framework->getAdapter(Controller::class)->loadDataContainer($this->filter->dataContainer);
        $this->framework->getAdapter(System::class)->loadLanguageFile($this->filter->dataContainer);

        if (null !== ($listClass = $this->manager->getListByName($this->listConfig->list ?: 'default'))) {
            $reflection = new \ReflectionClass($listClass);

            if (!$reflection->implementsInterface(ListInterface::class)) {
                throw new \Exception(sprintf('List class %s must implement %s', $listClass, ListInterface::class));
            }

            if (!$reflection->implementsInterface(\JsonSerializable::class)) {
                throw new \Exception(sprintf('List class %s must implement %s', $listClass, \JsonSerializable::class));
            }

            $this->manager->setList(new $listClass($this->manager));
        }

        /**
         * @var \Symfony\Component\EventDispatcher\EventDispatcher
         */
        $dispatcher = \System::getContainer()->get('event_dispatcher');
        $dispatcher->addListener(ListModifyQueryBuilderEvent::NAME, [$this, 'listModifyQueryBuilder']);

        if (true === (bool) $this->manager->getListConfig()->doNotRenderEmpty && empty($this->manager->getList()->getItems())) {
            /** @var FilterQueryBuilder $queryBuilder */
            $queryBuilder = $this->manager->getFilterManager()->getQueryBuilder($this->filter->id);
            $fields = $this->filter->dataContainer.'.* ';

            if ($totalCount = $queryBuilder->select($fields)->execute()->rowCount() < 1) {
                return '';
            }
        }

        return parent::generate();
    }

    /**
     * Modify the list query builder.
     */
    public function listModifyQueryBuilder(ListModifyQueryBuilderEvent $event)
    {
        $filter = (object) $event->getList()->getManager()->getFilterConfig()->getFilter();

        $pk = 'id';
        $model = Model::getClassFromTable($filter->dataContainer);

        /** @var Model $model */
        if (class_exists($model)) {
            $pk = $model::getPk();
        }

        $queryBuilder = $event->getQueryBuilder();

        $values = array_filter(StringUtil::deserialize($this->objModel->listPreselect, true));

        $queryBuilder->andWhere(System::getContainer()->get('huh.utils.database')->composeWhereForQueryBuilder($queryBuilder, $this->filter->dataContainer.'.'.$pk, DatabaseUtil::OPERATOR_IN, $GLOBALS['TL_DCA'][$filter->dataContainer], $values));

        $queryBuilder->add(
            'orderBy',
            sprintf(
                'FIELD(%s, %s)',
                $filter->dataContainer.'.'.$pk,
                implode(
                    ',',
                    array_map(
                        function ($val) {
                            return '"'.addslashes(Controller::replaceInsertTags(trim($val), false)).'"';
                        },
                        $values
                    )
                )
            )
        );

        $event->setQueryBuilder($queryBuilder);
    }

    /**
     * {@inheritdoc}
     */
    protected function compile()
    {
        $this->Template->noSearch = (bool) $this->manager->getListConfig()->noSearch;

        $this->Template->list = function (string $listTemplate = null, string $itemTemplate = null, array $data = []) {
            return $this->manager->getList()->parse($listTemplate, $itemTemplate, $data);
        };
    }

    /**
     * Invoke preselection.
     */
    protected function preselect()
    {
        if (null === ($filterConfig = System::getContainer()->get('huh.filter.manager')->findById($this->filterConfig->getId())) || null === ($elements = $filterConfig->getElements())) {
            return;
        }

        /** @var FilterPreselectModel $preselections */
        $preselections = System::getContainer()->get('contao.framework')->createInstance(FilterPreselectModel::class);

        if (null === ($preselections = $preselections->findPublishedByPidAndTableAndField($this->id, 'tl_content', 'filterPreselect'))) {
            $filterConfig->resetData(); // reset previous filters
            return;
        }

        $data = System::getContainer()->get('huh.filter.util.filter_preselect')->getPreselectData($this->filterConfig->getId(), $preselections->getModels());

        $filterConfig->setData($data);
    }
}

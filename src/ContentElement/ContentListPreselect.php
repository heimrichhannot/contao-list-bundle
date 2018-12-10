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
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Model\FilterPreselectModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\ListBundle\Event\ListModifyQueryBuilderEvent;
use HeimrichHannot\ListBundle\Exception\InterfaceNotImplementedException;
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

    /**
     * ContentListPreselect constructor.
     *
     * @param ContentModel $objElement
     * @param string       $strColumn
     *
     * @codeCoverageIgnore
     */
    public function __construct(ContentModel $objElement, string $strColumn = 'main')
    {
        parent::__construct($objElement, $strColumn);

        $this->listConfigRegistry = System::getContainer()->get('huh.list.list-config-registry');
        $this->request = System::getContainer()->get('huh.request');

        // retrieve list config
        $this->listConfig = System::getContainer()->get('huh.list.list-config-registry')->getComputedListConfig((int) $objElement->listConfig);

        $this->manager = System::getContainer()->get('huh.list.util.manager')->getListManagerByName($this->listConfig->manager ?: 'default');
        $this->manager->setListConfig($this->listConfig);
        $this->manager->setModuleData($this->arrData);

        $this->filterConfig = $this->manager->getFilterConfig();
        $this->filter = (object) $this->filterConfig->getFilter();
    }

    /**
     * @codeCoverageIgnore
     */
    public function generate()
    {
        if (!$this->doGenerate()) {
            return '';
        }

        return parent::generate();
    }

    public function doGenerate()
    {
        if (!$this->manager) {
            return false;
        }

        $values = array_filter(StringUtil::deserialize($this->objModel->listPreselect, true));

        if (empty($values)) {
            return false;
        }

        $this->preselect();

        $framework = System::getContainer()->get('contao.framework');
        $framework->getAdapter(Controller::class)->loadDataContainer('tl_list_config');
        $framework->getAdapter(Controller::class)->loadDataContainer($this->filter->dataContainer);
        $framework->getAdapter(Controller::class)->loadLanguageFile($this->filter->dataContainer);

        if ($listClass = $this->manager->getListByName($this->listConfig->list ?: 'default')) {
            $reflection = new \ReflectionClass($listClass);

            if (!$reflection->implementsInterface(ListInterface::class)) {
                throw new InterfaceNotImplementedException(ListInterface::class, $listClass);
            }

            if (!$reflection->implementsInterface(\JsonSerializable::class)) {
                throw new InterfaceNotImplementedException(\JsonSerializable::class, $listClass);
            }

            $this->manager->setList(new $listClass($this->manager));
        }

        /**
         * @var \Symfony\Component\EventDispatcher\EventDispatcher
         */
        $dispatcher = System::getContainer()->get('event_dispatcher');
        $dispatcher->addListener(ListModifyQueryBuilderEvent::NAME, [$this, 'listModifyQueryBuilder']);

        if (true === (bool) $this->manager->getListConfig()->doNotRenderEmpty
            && empty($this->manager->getList()->getItems())) {
            /** @var FilterQueryBuilder $queryBuilder */
            $queryBuilder = $this->manager->getFilterManager()->getQueryBuilder($this->filter->id);

            if (!$queryBuilder) {
                return false;
            }
            $fields = $this->filter->dataContainer.'.* ';

            if ($totalCount = $queryBuilder->select($fields)->execute()->rowCount() < 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Modify the list query builder.
     */
    public function listModifyQueryBuilder(ListModifyQueryBuilderEvent $event)
    {
        $framework = System::getContainer()->get('contao.framework');
        $filter = (object) $event->getList()->getManager()->getFilterConfig()->getFilter();

        $pk = 'id';
        $model = $framework->getAdapter(Model::class)->getClassFromTable($filter->dataContainer);

        /** @var Model $model */
        if (class_exists($model)) {
            $pk = $model::getPk();
        }

        $queryBuilder = $event->getQueryBuilder();

        $values = array_filter(StringUtil::deserialize($this->objModel->listPreselect, true));

        $queryBuilder->andWhere(System::getContainer()->get('huh.utils.database')->composeWhereForQueryBuilder(
            $queryBuilder,
            $this->filter->dataContainer.'.'.$pk,
            DatabaseUtil::OPERATOR_IN,
            $GLOBALS['TL_DCA'][$filter->dataContainer],
            $values
        ));

        $queryBuilder->add(
            'orderBy',
            sprintf(
                'FIELD(%s, %s)',
                $filter->dataContainer.'.'.$pk,
                implode(
                    ',',
                    array_map(
                        function ($val) use (&$framework) {
                            return '"'.addslashes($framework->getAdapter(Controller::class)->replaceInsertTags(trim($val), false)).'"';
                        },
                        $values
                    )
                )
            )
        );

        $event->setQueryBuilder($queryBuilder);

        // always remove listener afterwards in order to add query not again on next content element
        $dispatcher = System::getContainer()->get('event_dispatcher');
        $dispatcher->removeListener(ListModifyQueryBuilderEvent::NAME, [$this, 'listModifyQueryBuilder']);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
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
        $filterConfig = System::getContainer()->get('huh.filter.manager')->findById($this->filterConfig->getId());

        if (!$filterConfig || !$elements = $filterConfig->getElements()) {
            return;
        }

        /** @var FilterPreselectModel $preselectModel */
        $preselectModel = System::getContainer()->get('contao.framework')->createInstance(FilterPreselectModel::class);

        if (null === ($preselections = $preselectModel->findPublishedByPidAndTableAndField($this->id, 'tl_content', 'filterPreselect'))) {
            $filterConfig->resetData(); // reset previous filters
            return;
        }

        $data = System::getContainer()->get('huh.filter.util.filter_preselect')
            ->getPreselectData($this->filterConfig->getId(), $preselections->getModels());

        $filterConfig->setData($data);
    }
}

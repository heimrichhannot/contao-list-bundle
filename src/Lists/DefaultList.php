<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Lists;

use Contao\Config;
use Contao\Database;
use Contao\Date;
use Contao\FrontendTemplate;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\Blocks\BlockModuleModel;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\ListBundle\Backend\ListConfig;
use HeimrichHannot\ListBundle\DataContainer\ListConfigElementContainer;
use HeimrichHannot\ListBundle\Event\ListAfterParseItemsEvent;
use HeimrichHannot\ListBundle\Event\ListAfterRenderEvent;
use HeimrichHannot\ListBundle\Event\ListBeforeParseItemsEvent;
use HeimrichHannot\ListBundle\Event\ListBeforeRenderEvent;
use HeimrichHannot\ListBundle\Event\ListModifyQueryBuilderEvent;
use HeimrichHannot\ListBundle\Event\ListModifyQueryBuilderForCountEvent;
use HeimrichHannot\ListBundle\HeimrichHannotContaoListBundle;
use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\ListBundle\ListConfiguration\ListConfiguration;
use HeimrichHannot\ListBundle\ListExtension\DcMultilingualListExtension;
use HeimrichHannot\ListBundle\ListExtension\ListExtensionCollection;
use HeimrichHannot\ListBundle\Manager\ListManagerInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\ListBundle\Pagination\RandomPagination;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\EventDispatcher\EventDispatcher;

class DefaultList implements ListInterface, \JsonSerializable
{
    const JUMP_TO_OVERVIEW_LABEL_DEFAULT = 'huh.list.labels.overview.default';

    /**
     * Current List Manager.
     *
     * @var ListManagerInterface
     */
    protected $_manager;

    /**
     * Current List Manager.
     *
     * @var EventDispatcher
     */
    protected $_dispatcher;

    /**
     * @var string
     */
    protected $_wrapperId;

    /**
     * @var string
     */
    protected $_itemsId;

    /**
     * @var string
     */
    protected $_dataAttributes;

    /**
     * @var bool
     */
    protected $_showInitialResults;

    /**
     * @var bool
     */
    protected $_isSubmitted;

    /**
     * @var bool
     */
    protected $_showItemCount;

    /**
     * @var string
     */
    protected $_itemsFoundText;

    /**
     * @var array
     */
    protected $_items;

    /**
     * @var array
     */
    protected $_rawItems;

    /**
     * @var string
     */
    protected $_pagination;

    /**
     * @var bool
     */
    protected $_showNoItemsText;

    /**
     * @var string
     */
    protected $_noItemsText;

    /**
     * @var array
     */
    protected $_masonryStampContentElements;

    /**
     * @var array
     */
    protected $_header;

    /**
     * @var bool
     */
    protected $_sortingHeader;

    /**
     * Current page.
     *
     * @var int
     */
    protected $_page = 0;

    /**
     * @var FilterConfig
     */
    protected $_filterConfig;

    /**
     * overview page.
     *
     * @var bool
     */
    protected $_addOverview = false;

    /**
     * @var string
     */
    protected $_jumpToOverview;

    /**
     * @var string
     */
    protected $_jumpToOverviewMultilingual;

    /**
     * @var string
     */
    protected $_jumpToOverviewLabel;

    /**
     * @var string
     */
    protected $_modal;

    /**
     * @var bool
     */
    protected $_addDetails;

    /**
     * @var array
     */
    private $_paginationData;

    /**
     * Constructor.
     */
    public function __construct(ListManagerInterface $_manager)
    {
        $this->_manager = $_manager;
        $this->_dispatcher = System::getContainer()->get('event_dispatcher');
    }

    public function parse(string $listTemplate = null, string $itemTemplate = null, array $data = []): ?string
    {
        $listConfig = $this->_manager->getListConfig();

        if (System::getContainer()->getParameter('kernel.debug')) {
            $stopwatch = System::getContainer()->get('debug.stopwatch');
            $stopwatch->start('huh.list.parse (ID '.$listConfig->id.')');
        }

        $isSubmitted = $this->_manager->getFilterConfig()->hasData();
        $filter = (object) $this->_manager->getFilterConfig()->getFilter();
        $this->_filterConfig = $this->_manager->getFilterConfig();

        $listConfiguration = new ListConfiguration($filter->dataContainer, $listConfig);

        $listExtensions = System::getContainer()->get(ListExtensionCollection::class)->getEnabledExtensionsForContext($listConfiguration);

        foreach ($listExtensions as $extension) {
            $this->_dispatcher->addListener(ListModifyQueryBuilderForCountEvent::NAME, [$extension, 'onListModifyQueryBuilderForCountEvent']);
        }

        System::getContainer()->get('huh.utils.dca')->loadDc($filter->dataContainer);
        $dca = &$GLOBALS['TL_DCA'][$filter->dataContainer];

        $this->setWrapperId('huh-list-'.$this->getModule()['id']);
        $this->setItemsId($this->getWrapperId().'-items');

        $this->addDataAttributes();
        $this->addMasonry();
        $this->addModal();
        $this->setAddDetails($listConfig->addDetails);

        if ($listConfig->addOverview) {
            $this->addJumpToOverview($listConfig);
        }

        if ($listConfig->isTableList) {
            $this->setSortingHeader($listConfig->sortingHeader);

            if ($listConfig->hasHeader) {
                $this->setHeader($this->generateTableHeader());
            }
        }

        // apply filter
        /** @var FilterQueryBuilder $queryBuilder */
        $queryBuilder = $this->_manager->getFilterManager()->getQueryBuilder($filter->id);

        // compute fields
        /** @var Database $db */
        $db = $this->_manager->getFramework()->createInstance(Database::class);

        $dbFields = $db->getFieldNames($filter->dataContainer);

//        // support for terminal42/contao-DC_Multilingual
//        if ($this->isDcMultilingualActive($listConfig, $dca, $filter->dataContainer)) {
//            $extension = System::getContainer()->get(ListExtensionCollection::class)->getExtension(DcMultilingualListExtension::getAlias());
//            $extension->prepareQueryBuilder($queryBuilder, $listConfiguration);
//            $fields = implode(', ', $queryBuilder->getQueryPart('select'));
//        }
        // support for heimrichhannot/contao-multilingual-fields-bundle
        if ($this->isMultilingualFieldsActive($listConfig, $filter->dataContainer)) {
            $fallbackLanguage = System::getContainer()->getParameter('huh_multilingual_fields')['fallback_language'];

            if ($GLOBALS['TL_LANGUAGE'] !== $fallbackLanguage) {
                // compute fields
                $fieldNames = [];

                foreach ($dca['fields'] as $field => $data) {
                    if (!isset($data['sql']) || isset($data['eval']['translatedField'])) {
                        continue;
                    }

                    if (isset($data['eval']['isTranslatedField'])) {
                        $selectorField = $data['eval']['translationConfig'][$GLOBALS['TL_LANGUAGE']]['selector'];
                        $translationField = $data['eval']['translationConfig'][$GLOBALS['TL_LANGUAGE']]['field'];

                        $fieldNames[] = "IF($filter->dataContainer.$selectorField=1, $filter->dataContainer.$translationField, $filter->dataContainer.$field) AS '$field'";
                    } else {
                        $fieldNames[] = $filter->dataContainer.'.'.$field;
                    }
                }

                $fields = implode(', ', $fieldNames);
            } else {
                $fields = implode(', ', array_map(function ($field) use ($filter) {
                    return $filter->dataContainer.'.'.$field;
                }, $dbFields));
            }
        } else {
            $fields = implode(', ', array_map(function ($field) use ($filter) {
                return $filter->dataContainer.'.'.$field;
            }, $dbFields));
        }

        // filter related items
        if (isset($GLOBALS['HUH_LIST_RELATED'])) {
            $this->addRelatedFilters($filter->dataContainer, $queryBuilder);
        }

        $this->setIsSubmitted($isSubmitted);

        $totalCount = 0;

        $event = $this->_dispatcher->dispatch(
            new ListModifyQueryBuilderForCountEvent($queryBuilder, $this, $listConfig, $fields, $listConfiguration),
            ListModifyQueryBuilderForCountEvent::NAME
        );

        // initial results
        $this->setShowInitialResults($listConfig->showInitialResults);

        if ($isSubmitted || $listConfig->showInitialResults) {
            $totalCount = $queryBuilder->select($filter->dataContainer.'.id')->execute()->rowCount();
            $queryBuilder->select($event->getFields());
        }

        // item count text
        $this->setShowItemCount($listConfig->showItemCount);

        $this->setItemsFoundText(System::getContainer()->get('translator')->trans($listConfig->itemCountText ?: 'huh.list.count.text.default', ['%count%' => $totalCount]));

        // no items text
        $this->setShowNoItemsText($listConfig->showNoItemsText);
        $this->setNoItemsText(System::getContainer()->get('translator')->trans($listConfig->noItemsText ?: 'huh.list.empty.text.default'));

        // query builder
        $this->applyListConfigToQueryBuilder($totalCount, $queryBuilder);

        $this->_dispatcher->dispatch(
            new ListModifyQueryBuilderEvent($queryBuilder, $this, $listConfig, $fields),
            ListModifyQueryBuilderEvent::NAME
        );

        if ($isSubmitted || $listConfig->showInitialResults) {
            $items = $queryBuilder->execute()->fetchAll();

            // add fields without sql key in DCA (could have a value by load_callback)
            foreach ($items as &$item) {
                $itemFields = array_keys($item);

                foreach (array_keys($dca['fields']) as $field) {
                    if (!\in_array($field, $itemFields)) {
                        $item[$field] = null;
                    }
                }
            }

            $this->setItems($this->parseItems($items, $itemTemplate));
            $this->setRawItems($items);
        }

        // render
        $listTemplate = $this->_manager->getListTemplateByName(($listTemplate ?: $listConfig->listTemplate) ?: 'default');
        $templateData = $this->jsonSerialize();

        $event = $this->_dispatcher->dispatch(
            new ListBeforeRenderEvent($templateData, $this, $listConfig),
            ListBeforeRenderEvent::NAME
        );

        $rendered = System::getContainer()->get('twig')->render($listTemplate, $event->getTemplateData());

        $event = $this->_dispatcher->dispatch(
            new ListAfterRenderEvent($rendered, $event->getTemplateData(), $this, $listConfig),
            ListAfterRenderEvent::NAME
        );

        $buffer = $event->getRendered();

        if (Config::get('debugMode')) {
            $buffer = "\n<!-- LIST TEMPLATE START: $listTemplate -->\n$buffer\n<!-- LIST TEMPLATE END: $listTemplate -->\n";
        }

        foreach ($listExtensions as $extension) {
            $this->_dispatcher->removeListener(ListModifyQueryBuilderForCountEvent::NAME, [$extension, 'onListModifyQueryBuilderForCountEvent']);
        }

        if (isset($stopwatch)) {
            $stopwatch->stop('huh.list.parse (ID '.$listConfig->id.')');
        }

        return $buffer;
    }

    public function addRelatedFilters(string $table, $queryBuilder)
    {
        // tags
        if (isset($GLOBALS['HUH_LIST_RELATED'][ListConfigElementContainer::RELATED_CRITERIUM_TAGS])) {
            $itemIds = $GLOBALS['HUH_LIST_RELATED'][ListConfigElementContainer::RELATED_CRITERIUM_TAGS]['itemIds'];

            // if no items with the given tags are found, no related news should be displayed
            $queryBuilder->andWhere($queryBuilder->expr()->in($table.'.id', empty($itemIds) ? [0] : $itemIds));
        }

        // categories
        if (isset($GLOBALS['HUH_LIST_RELATED'][ListConfigElementContainer::RELATED_CRITERIUM_CATEGORIES])) {
            $itemIds = $GLOBALS['HUH_LIST_RELATED'][ListConfigElementContainer::RELATED_CRITERIUM_CATEGORIES]['itemIds'];

            // if no items with the given tags are found, no related news should be displayed
            $queryBuilder->andWhere($queryBuilder->expr()->in($table.'.id', empty($itemIds) ? [0] : $itemIds));
        }
    }

    public function isMultilingualFieldsActive(ListConfigModel $listConfig, string $table)
    {
        if (!$listConfig->addMultilingualFieldsSupport) {
            return false;
        }

        $config = System::getContainer()->getParameter('huh_multilingual_fields');

        return isset($config['data_containers'][$table]);
    }

    public function isDcMultilingualActive(ListConfigModel $listConfig, array $dca, string $table)
    {
        return $listConfig->addDcMultilingualSupport && System::getContainer()->get('huh.utils.dca')->isDcMultilingual($table);
    }

    public function isDcMultilingualUtilsActive(ListConfigModel $listConfig, array $dca, string $table)
    {
        return $listConfig->addDcMultilingualSupport && System::getContainer()->get('huh.utils.dca')->isDcMultilingual($table) &&
            System::getContainer()->get('huh.utils.container')->isBundleActive('HeimrichHannot\DcMultilingualUtilsBundle\ContaoDcMultilingualUtilsBundle');
    }

    /**
     * {@inheritdoc}
     */
    public function parseItems(array $items, string $itemTemplate = null): array
    {
        $listConfig = $this->_manager->getListConfig();

        $limit = \count($items);

        if ($limit < 1) {
            return [];
        }

        $count = 0;
        $results = [];

        /** @var ListBeforeParseItemsEvent $event */
        $event = $this->_dispatcher->dispatch(
            new ListBeforeParseItemsEvent($items, $this, $listConfig),
            ListBeforeParseItemsEvent::NAME
        );

        $items = $event->getItems() ?: [];

        foreach ($items as $key => $item) {
            // reset the list config since it might have been reset while parsing the items
            $this->_manager->setListConfig($listConfig);

            ++$count;
            $first = 1 == $count ? ' first' : '';
            $last = $count == $limit ? ' last' : '';
            $oddEven = (0 == ($count % 2)) ? ' even' : ' odd';

            $cssClass = 'item item_'.$count.$first.$last.$oddEven;

            if (null !== ($itemClass = $this->getItemClassByName($listConfig->item ?: 'default'))) {
                $interfaces = class_implements($itemClass);

                if (false === $interfaces) {
                    throw new \Exception('Class '.$itemClass.' does not exist!');
                }

                if (!isset($interfaces[ItemInterface::class])) {
                    throw new \Exception(sprintf('Item class %s must implement %s', $itemClass, ItemInterface::class));
                }

                if (!isset($interfaces[\JsonSerializable::class])) {
                    throw new \Exception(sprintf('Item class %s must implement %s', $itemClass, \JsonSerializable::class));
                }

                /** @var ItemInterface $result */
                $result = new $itemClass($this->_manager, $item);
            } else {
                throw new \Exception(sprintf('Item class for %s not found', $listConfig->item ?: 'default', \JsonSerializable::class));
            }

            $parsedResult = $result->parse($cssClass, $count);

            if (empty(trim($parsedResult))) {
                --$count;

                continue;
            }

            $results[$key] = $parsedResult;
        }

        /** @var ListAfterParseItemsEvent $event */
        $event = $this->_dispatcher->dispatch(
            new ListAfterParseItemsEvent($items, $results, $this, $listConfig),
            ListAfterParseItemsEvent::NAME
        );

        $this->setRawItems($event->getItems());

        return $event->getParsedItems();
    }

    public function applyListConfigToQueryBuilder(int $totalCount, FilterQueryBuilder $queryBuilder): void
    {
        $listConfig = $this->_manager->getListConfig();
        $filter = (object) $this->_manager->getFilterConfig()->getFilter();

        // offset
        $offset = (int) ($listConfig->skipFirst);

        // limit
        $limit = null;

        if ($listConfig->numberOfItems > 0) {
            $limit = $listConfig->numberOfItems;
        }

        $queryParts = $queryBuilder->getQueryParts();

        if (!empty($queryParts['orderBy'])) {
            [$offset, $limit] = $this->splitResults($offset, $totalCount, $limit);
            // split the results
            $queryBuilder->setFirstResult($offset)->setMaxResults($limit);

            return;
        }
        // sorting
        $currentSorting = $this->_manager->getCurrentSorting();

        if (ListConfig::SORTING_MODE_RANDOM == $currentSorting['order']) {
            $randomSeed = $this->_manager->getRequest()->getGet(RandomPagination::PARAM_RANDOM) ?: rand(1, 500);
            $queryBuilder->orderBy('RAND("'.(int) $randomSeed.'")');
            [$offset, $limit] = $this->splitResults($offset, $totalCount, $limit, $randomSeed);
        } elseif (ListConfig::SORTING_MODE_MANUAL == $currentSorting['order']) {
            $sortingItems = StringUtil::deserialize($listConfig->sortingItems, true);

            if (!empty($sortingItems)) {
                $queryBuilder->orderBy('FIELD('.$filter->dataContainer.'.id,'.implode(',', $sortingItems).')', ' ');
            }

            [$offset, $limit] = $this->splitResults($offset, $totalCount, $limit);
        } else {
            if (!empty($currentSorting)) {
                $queryBuilder->orderBy($currentSorting['order'], $currentSorting['sort']);
            }

            [$offset, $limit] = $this->splitResults($offset, $totalCount, $limit);
        }

        // split the results
        $queryBuilder->setFirstResult($offset)->setMaxResults($limit);
    }

    public function splitResults($offset, $total, $limit, $randomSeed = null): ?array
    {
        $listConfig = $this->_manager->getListConfig();
        $offsettedTotal = $total - $offset;

        // Split the results
        if ($listConfig->perPage > 0 && (!isset($limit) || $listConfig->numberOfItems > $listConfig->perPage)) {
            // Adjust the overall limit
            if (isset($limit)) {
                $offsettedTotal = min($limit, $offsettedTotal);
            }

            // Get the current page
            $id = 'page_s'.$this->getModule()['id'];
            $page = (int) $this->_manager->getRequest()->getGet($id) ?: 1;
            $this->setPage($page);

            $pageModel = System::getContainer()->get(Utils::class)->request()->getCurrentPageModel();

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($offsettedTotal / $listConfig->perPage), 1)) {
                $pageModel->noSearch = 1;
                $pageModel->cache = 0;

                // Send a 404 header
                header('HTTP/1.1 404 Not Found');

                return null;
            }

            // Set limit and offset
            $limit = $listConfig->perPage;
            $offset += (max($page, 1) - 1) * $listConfig->perPage;
            $skip = (int) $listConfig->skipFirst;

            // Overall limit
            if ($offset + $limit > $offsettedTotal + $skip) {
                $limit = $offsettedTotal + $skip - $offset;
            }

            if ($limit < 0) {
                $limit = 0;
            }

            // Add the pagination menu
            if ($listConfig->addAjaxPagination) {
                $pagination = new RandomPagination($randomSeed, $offsettedTotal, $listConfig->perPage, Config::get('maxPaginationLinks'), $id, new FrontendTemplate($listConfig->ajaxPaginationTemplate ?: 'pagination_list_ajax'));
            } else {
                $pagination = new RandomPagination($randomSeed, $offsettedTotal, $listConfig->perPage, Config::get('maxPaginationLinks'), $id);
            }

            $this->setPagination($pagination->generate("\n  "));
            $this->setPaginationData($pagination->getTemplate()->getData());

            if ($page > 1) {
                $pageModel->robots = 'noindex,follow';
            }
        }

        return [$offset, $limit];
    }

    /**
     * {@inheritdoc}
     */
    public function generateTableHeader(): array
    {
        $headerFields = [];
        $currentSorting = $this->_manager->getCurrentSorting();
        $listConfig = $this->_manager->getListConfig();
        $filter = (object) $this->_manager->getFilterConfig()->getFilter();
        $urlUtil = System::getContainer()->get('huh.utils.url');
        $dca = &$GLOBALS['TL_DCA'][$filter->dataContainer];
        $tableFields = \Contao\StringUtil::deserialize($listConfig->tableFields, true);

        foreach ($tableFields as $i => $name) {
            $isCurrentOrderField = ($name == $currentSorting['order']);

            $field = [
                'label' => $dca['fields'][$name]['label'][0] ?: $name,
                'class' => System::getContainer()->get('huh.utils.string')->camelCaseToDashed($name),
            ];

            if ($isCurrentOrderField) {
                $field['sortingClass'] = (ListConfig::SORTING_DIRECTION_ASC == $currentSorting['sort'] ? ListConfig::SORTING_DIRECTION_ASC : ListConfig::SORTING_DIRECTION_DESC);

                $field['link'] = $urlUtil->addQueryString('order='.$name.'&sort='.(ListConfig::SORTING_DIRECTION_ASC == $currentSorting['sort'] ? ListConfig::SORTING_DIRECTION_DESC : ListConfig::SORTING_DIRECTION_ASC));
            } else {
                $field['link'] = $urlUtil->addQueryString('order='.$name.'&sort='.ListConfig::SORTING_DIRECTION_ASC);
            }

            $headerFields[] = $field;
        }

        return $headerFields;
    }

    public function addDataAttributes()
    {
        $dataAttributes = [];
        $stringUtil = System::getContainer()->get('huh.utils.string');
        $listConfig = $this->_manager->getListConfig();

        foreach ($GLOBALS['TL_DCA']['tl_list_config']['fields'] as $field => $data) {
            if (($data['eval']['addAsDataAttribute'] ?? false) && $listConfig->{$field}) {
                $dataAttributes[] = 'data-'.$stringUtil->camelCaseToDashed($field).'="'.$listConfig->{$field}.'"';
            }
        }

        if (!empty($dataAttributes)) {
            $this->setDataAttributes(implode(' ', $dataAttributes));
        }
    }

    public function addMasonry()
    {
        $listConfig = $this->_manager->getListConfig();

        if ($listConfig->addMasonry) {
            $contentElements = StringUtil::deserialize($listConfig->masonryStampContentElements, true);

            if (empty($contentElements)) {
                return;
            }

            $stamps = [];

            foreach ($contentElements as $stamp) {
                /** @var BlockModuleModel $blockModule */
                $blockModule = $this->_manager->getFramework()->getAdapter(BlockModuleModel::class);

                $stamps[] = [
                    'content' => $blockModule->generateContent($stamp['stampBlock']),
                    'class' => $stamp['stampCssClass'],
                ];
            }

            $this->setMasonryStampContentElements($stamps);
        }
    }

    public function addJumpToOverview(ListConfigModel $listConfig): void
    {
        $this->setAddOverview($listConfig->addOverview);

        $jumpToOverviewMultilingual = StringUtil::deserialize($listConfig->jumpToOverviewMultilingual, true);
        $jumpToOverview = $listConfig->jumpToOverview;

        if (!empty($jumpToOverviewMultilingual)) {
            foreach ($jumpToOverviewMultilingual as $item) {
                if (isset($item['language']) && $GLOBALS['TL_LANGUAGE'] === $item['language']) {
                    $jumpToOverview = $item['jumpTo'];

                    break;
                }
            }
        }

        $pageJumpTo = System::getContainer()->get('huh.utils.url')->getJumpToPageObject($jumpToOverview);

        if (null !== $pageJumpTo) {
            $this->setJumpToOverview($pageJumpTo->getAbsoluteUrl());
        }

        $this->setJumpToOverviewLabel($this->getTranslatedJumpToOverviewLabel($listConfig));
    }

    /**
     * {@inheritdoc}
     */
    public function handleShare()
    {
        $listConfig = $this->_manager->getListConfig();
        $filter = (object) $this->_manager->getFilterConfig();
        $request = $this->_manager->getRequest();
        $action = $request->getGet('act');

        if (HeimrichHannotContaoListBundle::ACTION_SHARE == $action && $listConfig->addShare) {
            $url = $request->getGet('url');
            $id = $request->getGet($listConfig->useAlias ? $listConfig->aliasField : 'id');

            if (null !== ($entity = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($this->_manager->getFramework(), $filter->dataContainer, $id))) {
                $now = time();

                if ($this->shareTokenExpiredOrEmpty($entity, $now)) {
                    $shareToken = str_replace('.', '', uniqid('', true));
                    $entity->shareToken = $shareToken;
                    $entity->shareTokenTime = $now;
                    $entity->save();
                }

                if ($listConfig->shareAutoItem) {
                    $shareUrl = $url.'/'.$entity->shareToken;
                } else {
                    $shareUrl = System::getContainer()->get('huh.utils.url')->addQueryString('share='.$entity->shareToken, $url);
                }

                exit($shareUrl);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function shareTokenExpiredOrEmpty($entity, $now)
    {
        $shareToken = $entity->shareToken;
        $expirationInterval = StringUtil::deserialize(Config::get('shareExpirationInterval'), true);
        $interval = 604800; // default: 7 days

        if (isset($expirationInterval['unit']) && isset($expirationInterval['value']) && $expirationInterval['value'] > 0) {
            $interval = System::getContainer()->get('huh.utils.date')->getTimePeriodInSeconds($expirationInterval);
        }

        return !$shareToken || !$entity->shareTokenTime || ($entity->shareTokenTime > $now + $interval);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemClassByName(string $name)
    {
        $config = System::getContainer()->getParameter('huh.list');

        if (!isset($config['list']['items'])) {
            return null;
        }

        $items = $config['list']['items'];

        foreach ($items as $item) {
            if ($item['name'] == $name) {
                return class_exists($item['class']) ? $item['class'] : null;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getManager(): ListManagerInterface
    {
        return $this->_manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataContainer(): ?string
    {
        $filter = (object) $this->_manager->getFilterConfig()->getFilter();

        return $filter->dataContainer;
    }

    /**
     * {@inheritdoc}
     */
    public function getModule(): ?array
    {
        return $this->_manager->getModuleData();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return System::getContainer()->get('huh.utils.class')->jsonSerialize($this);
    }

    /**
     * @return string
     */
    public function getWrapperId(): ?string
    {
        return $this->_wrapperId;
    }

    public function setWrapperId(string $wrapperId)
    {
        $this->_wrapperId = $wrapperId;
    }

    /**
     * @return string
     */
    public function getDataAttributes(): ?string
    {
        return $this->_dataAttributes;
    }

    public function setDataAttributes(string $dataAttributes)
    {
        $this->_dataAttributes = $dataAttributes;
    }

    /**
     * @return bool
     */
    public function isShowInitialResults(): ?bool
    {
        return $this->_showInitialResults;
    }

    public function setShowInitialResults(bool $showInitialResults)
    {
        $this->_showInitialResults = $showInitialResults;
    }

    /**
     * @return bool
     */
    public function isSubmitted(): ?bool
    {
        return $this->_isSubmitted;
    }

    public function setIsSubmitted(bool $isSubmitted)
    {
        $this->_isSubmitted = $isSubmitted;
    }

    /**
     * @return bool
     */
    public function isShowItemCount(): ?bool
    {
        return $this->_showItemCount;
    }

    public function setShowItemCount(bool $showItemCount)
    {
        $this->_showItemCount = $showItemCount;
    }

    /**
     * @return string
     */
    public function getItemsFoundText(): ?string
    {
        return $this->_itemsFoundText;
    }

    public function setItemsFoundText(string $itemsFoundText)
    {
        $this->_itemsFoundText = $itemsFoundText;
    }

    /**
     * @return array
     */
    public function getItems(): ?array
    {
        return $this->_items;
    }

    public function setItems(array $items)
    {
        $this->_items = $items;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawItems(): ?array
    {
        return $this->_rawItems;
    }

    /**
     * {@inheritdoc}
     */
    public function setRawItems(array $items)
    {
        $this->_rawItems = $items;
    }

    /**
     * @return string
     */
    public function getPagination(): ?string
    {
        return $this->_pagination;
    }

    public function setPagination(string $pagination)
    {
        $this->_pagination = $pagination;
    }

    /**
     * @return bool
     */
    public function isShowNoItemsText(): ?bool
    {
        return $this->_showNoItemsText;
    }

    public function setShowNoItemsText(bool $showNoItemsText)
    {
        $this->_showNoItemsText = $showNoItemsText;
    }

    /**
     * @return string
     */
    public function getNoItemsText(): ?string
    {
        return $this->_noItemsText;
    }

    public function setNoItemsText(string $noItemsText)
    {
        $this->_noItemsText = $noItemsText;
    }

    /**
     * @return array
     */
    public function getMasonryStampContentElements(): ?array
    {
        return $this->_masonryStampContentElements;
    }

    public function setMasonryStampContentElements(array $masonryStampContentElements)
    {
        $this->_masonryStampContentElements = $masonryStampContentElements;
    }

    /**
     * @return array
     */
    public function getHeader(): ?array
    {
        return $this->_header;
    }

    public function setHeader(array $header)
    {
        $this->_header = $header;
    }

    /**
     * @return bool
     */
    public function isSortingHeader(): ?bool
    {
        return $this->_sortingHeader;
    }

    public function setSortingHeader(bool $sortingHeader)
    {
        $this->_sortingHeader = $sortingHeader;
    }

    /**
     * {@inheritdoc}
     */
    public function setPage(int $page): void
    {
        $this->_page = $page;
    }

    /**
     * {@inheritdoc}
     */
    public function getPage(): int
    {
        return $this->_page;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchablePages(array $arrPages, int $intRoot = 0, bool $blnIsSitemap = false): array
    {
        if (!$this->getJumpTo()) {
            return $arrPages;
        }

        $arrRoot = [];

        if ($intRoot > 0) {
            /** @var Database $database */
            $database = $this->_manager->getFramework()->createInstance(Database::class);
            $arrRoot = $database->getChildRecords($intRoot, 'tl_page');
        }

        $this->_manager->getFilterConfig()->resetData();

        $listConfig = $this->_manager->getListConfig();

        $multilingualJumpTos = [];

        // multilingual jumpTos
        $jumpToDetailsMultilingual = StringUtil::deserialize($listConfig->jumpToDetailsMultilingual, true);

        foreach ($jumpToDetailsMultilingual as $data) {
            if (!$data['language'] || !$data['jumpTo'] || $intRoot > 0 && !\in_array($data['jumpTo'], $arrRoot)) {
                continue;
            }

            $multilingualJumpTos[] = $data['jumpTo'];
        }

        if ($intRoot > 0 && !empty($arrRoot) && empty(array_intersect(array_merge([$this->getJumpTo()], $multilingualJumpTos), $arrRoot))) {
            return $arrPages;
        }

        // support for multilingual initial filter values
        if (0 === $intRoot) {
            $time = time();
            $query = "SELECT id, language, sitemapName FROM tl_page WHERE type='root' AND published='1' AND (start='' OR start<='$time') AND (stop='' OR stop>'$time')";

            if ($blnIsSitemap) {
                $query .= " AND createSitemap='1' AND sitemapName!=''";
            }

            $rootPages = Database::getInstance()->execute($query);

            // Return if there are no pages
            if ($rootPages->numRows < 1) {
                return $arrPages;
            }

            while ($rootPages->next()) {
                $arrPages = $this->computeSearchablePages($listConfig, $arrRoot, $arrPages, $rootPages->id, $blnIsSitemap);
            }
        } else {
            $arrPages = $this->computeSearchablePages($listConfig, $arrRoot, $arrPages, $intRoot, $blnIsSitemap);
        }

        return $arrPages;
    }

    /**
     * {@inheritdoc}
     */
    public function getJumpTo(): int
    {
        return (int) $this->_manager->getListConfig()->jumpToDetails;
    }

    public function getFilterConfig(): ?FilterConfig
    {
        return $this->_filterConfig;
    }

    /**
     * @return string
     */
    public function getJumpToOverview(): ?string
    {
        return $this->_jumpToOverview;
    }

    public function setJumpToOverview(string $jumpToOverview): void
    {
        $this->_jumpToOverview = $jumpToOverview;
    }

    /**
     * @return string
     */
    public function getJumpToOverviewMultilingual(): ?string
    {
        return $this->_jumpToOverviewMultilingual;
    }

    public function setJumpToOverviewMultilingual(string $jumpToOverviewMultilingual): void
    {
        $this->_jumpToOverviewMultilingual = $jumpToOverviewMultilingual;
    }

    public function setAddOverview(bool $addOverview)
    {
        $this->_addOverview = $addOverview;
    }

    public function getAddOverview(): bool
    {
        return $this->_addOverview;
    }

    public function setJumpToOverviewLabel(string $label)
    {
        $this->_jumpToOverviewLabel = $label;
    }

    /**
     * @return string
     */
    public function getJumpToOverviewLabel(): ?string
    {
        return $this->_jumpToOverviewLabel;
    }

    public function getTranslatedJumpToOverviewLabel(ListConfigModel $listConfig): string
    {
        $label = $listConfig->customJumpToOverviewLabel ? $listConfig->jumpToOverviewLabel : static::JUMP_TO_OVERVIEW_LABEL_DEFAULT;

        return System::getContainer()->get('translator')->trans($label);
    }

    public function addModal()
    {
        $listConfig = $this->_manager->getListConfig();

        if (!$listConfig->openListItemsInModal) {
            return;
        }

        $templateName = $this->_manager->getItemTemplateByName($listConfig->listModalTemplate ?: 'list_modal_bs4');

        $this->setModal(System::getContainer()->get('twig')->render($templateName, [
            'module' => $this->getModule(),
        ]));
    }

    /**
     * @return string
     */
    public function getModal(): ?string
    {
        return $this->_modal;
    }

    public function setModal(string $modal): void
    {
        $this->_modal = $modal;
    }

    public function getItemsId(): string
    {
        return $this->_itemsId;
    }

    public function setItemsId(string $itemsId): void
    {
        $this->_itemsId = $itemsId;
    }

    public function isAddDetails(): bool
    {
        return $this->_addDetails;
    }

    public function setAddDetails(bool $addDetails): void
    {
        $this->_addDetails = $addDetails;
    }

    public function getListContextVariables(): array
    {
        return array_column(StringUtil::deserialize($this->_manager->getListConfig()->listContextVariables, true), 'value', 'key');
    }

    public function getPaginationData(): ?array
    {
        return $this->_paginationData;
    }

    public function setPaginationData(array $paginationData): void
    {
        $this->_paginationData = $paginationData;
    }

    protected function computeSearchablePages(Model $listConfig, array $arrRoot, array $arrPages, int $intRoot = 0, bool $blnIsSitemap = false)
    {
        $filter = (object) $this->_manager->getFilterConfig()->getFilter();
        $table = $filter->dataContainer;
        $dcMultilingualActive = System::getContainer()->get('huh.utils.dca')->isDcMultilingual($table);

        if (null === ($rootPage = System::getContainer()->get(ModelUtil::class)->findModelInstanceByPk('tl_page', $intRoot))) {
            return $arrPages;
        }

        // switch language for multilingual (language-dependent) initial filter field values
        $tmpLang = $GLOBALS['TL_LANGUAGE'];
        $GLOBALS['TL_LANGUAGE'] = $rootPage->language;

        /** @var FilterQueryBuilder $queryBuilder */
        $queryBuilder = $this->_manager->getFilterManager()->getQueryBuilder($filter->id);

        $fields = $table.'.* ';

        if (($totalCount = $queryBuilder->select($fields)->execute()->rowCount()) < 1) {
            return $arrPages;
        }

        $this->_dispatcher->dispatch(
            new ListModifyQueryBuilderEvent($queryBuilder, $this, $listConfig, $fields),
            ListModifyQueryBuilderEvent::NAME
        );

        $items = $queryBuilder->execute()->fetchAll();

        $GLOBALS['TL_LANGUAGE'] = $tmpLang;

        if (null !== ($itemClass = $this->getItemClassByName($listConfig->item ?: 'default'))) {
            $reflection = new \ReflectionClass($itemClass);

            if (!$reflection->implementsInterface(ItemInterface::class)) {
                return $arrPages;
            }
        }

        foreach ($items as $item) {
            /** @var ItemInterface $result */
            $result = new $itemClass($this->_manager, $item, false);

            // id or alias
            if (null === ($idOrAlias = $result->generateIdOrAlias($result, $listConfig))) {
                continue;
            }

            $urls = [];

            if (!$intRoot || $intRoot > 0 && \in_array($this->getJumpTo(), $arrRoot)) {
                $result->setIdOrAlias($idOrAlias);
                $result->addDetailsUrl($idOrAlias, $result, $listConfig, true);

                $url = $result->getDetailsUrl($blnIsSitemap);

                if (null !== $url && !empty($url)) {
                    $urls[] = $url;
                }
            }

            if (!empty($jumpToDetailsMultilingual)) {
                $tmpLang = $GLOBALS['TL_LANGUAGE'];

                foreach ($jumpToDetailsMultilingual as $data) {
                    if (!$data['language'] || !$data['jumpTo'] || $intRoot > 0 && !\in_array($data['jumpTo'], $arrRoot)) {
                        continue;
                    }

                    $GLOBALS['TL_LANGUAGE'] = $data['language'];

                    // switch the alias
                    if ($dcMultilingualActive) {
                        $query = "SELECT $table.$listConfig->aliasField FROM $table WHERE $table.langPid=? AND $table.language=?";

                        if ($this->isDcMultilingualUtilsActive($listConfig, [], $table)) {
                            $time = Date::floorToMinute();

                            $query .= " AND $table.langPublished=1 AND ($table.langStart = '' OR $table.langStart <= $time) AND ($table.langStop = '' OR $table.langStop > ".($time + 60).')';
                        }

                        $translatedResult = Database::getInstance()->prepare($query)->limit(1)->execute($result->getRawValue('id'), $data['language']);

                        if ($translatedResult->numRows < 1) {
                            continue;
                        }

                        $result->{$listConfig->aliasField} = $translatedResult->{$listConfig->aliasField};

                        if (null === ($idOrAlias = $result->generateIdOrAlias($result, $listConfig))) {
                            continue;
                        }
                    }

                    $result->addDetailsUrl($idOrAlias, $result, $listConfig, true);

                    $url = $result->getDetailsUrl($blnIsSitemap);

                    if (null !== $url && !empty($url)) {
                        $urls[] = $url;
                    }
                }

                $GLOBALS['TL_LANGUAGE'] = $tmpLang;
            }

            $arrPages = array_unique(array_merge($arrPages, $urls));
        }

        return $arrPages;
    }
}

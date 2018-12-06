<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Lists;

use Contao\Config;
use Contao\Controller;
use Contao\Database;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\Blocks\BlockModuleModel;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\ListBundle\Backend\ListConfig;
use HeimrichHannot\ListBundle\Event\ListAfterParseItemsEvent;
use HeimrichHannot\ListBundle\Event\ListAfterRenderEvent;
use HeimrichHannot\ListBundle\Event\ListBeforeParseItemsEvent;
use HeimrichHannot\ListBundle\Event\ListBeforeRenderEvent;
use HeimrichHannot\ListBundle\Event\ListModifyQueryBuilderEvent;
use HeimrichHannot\ListBundle\HeimrichHannotContaoListBundle;
use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\ListBundle\Manager\ListManagerInterface;
use HeimrichHannot\ListBundle\Pagination\RandomPagination;
use Symfony\Component\EventDispatcher\EventDispatcher;

class DefaultList implements ListInterface, \JsonSerializable
{
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
     * Constructor.
     *
     * @param ListManagerInterface $_manager
     */
    public function __construct(ListManagerInterface $_manager)
    {
        $this->_manager = $_manager;
        $this->_dispatcher = System::getContainer()->get('event_dispatcher');
    }

    public function parse(string $listTemplate = null, string $itemTemplate = null, array $data = []): ?string
    {
        $isSubmitted = $this->_manager->getFilterConfig()->hasData();
        $listConfig = $this->_manager->getListConfig();
        $filter = (object) $this->_manager->getFilterConfig()->getFilter();
        $this->_filterConfig = $this->_manager->getFilterConfig();

        $this->setWrapperId('huh-list-'.$this->getModule()['id']);

        $this->addDataAttributes();
        $this->addMasonry();

        if ($listConfig->isTableList) {
            $this->setSortingHeader($listConfig->sortingHeader);

            if ($listConfig->hasHeader) {
                $this->setHeader($this->generateTableHeader());
            }
        }

        // apply filter
        /** @var FilterQueryBuilder $queryBuilder */
        $queryBuilder = $this->_manager->getFilterManager()->getQueryBuilder($filter->id);

        $this->setIsSubmitted($isSubmitted);

        $fields = $filter->dataContainer.'.* ';

        $totalCount = 0;

        // initial results
        $this->setShowInitialResults($listConfig->showInitialResults);

        if ($isSubmitted || $listConfig->showInitialResults) {
            $totalCount = $queryBuilder->select($fields)->execute()->rowCount();
        }

        // item count text
        $this->setShowItemCount($listConfig->showItemCount);

        $this->setItemsFoundText(System::getContainer()->get('translator')->transChoice($listConfig->itemCountText ?: 'huh.list.count.text.default', $totalCount, ['%count%' => $totalCount]));

        // no items text
        $this->setShowNoItemsText($listConfig->showNoItemsText);
        $this->setNoItemsText(System::getContainer()->get('translator')->trans($listConfig->noItemsText ?: 'huh.list.empty.text.default'));

        // query builder
        $this->applyListConfigToQueryBuilder($totalCount, $queryBuilder);

        $this->_dispatcher->dispatch(ListModifyQueryBuilderEvent::NAME, new ListModifyQueryBuilderEvent($queryBuilder, $this, $listConfig));

        if ($isSubmitted || $listConfig->showInitialResults) {
            $items = $queryBuilder->execute()->fetchAll();

            // add fields without sql key in DCA (could have a value by load_callback)
            Controller::loadDataContainer($filter->dataContainer);

            foreach ($items as &$item) {
                $itemFields = array_keys($item);

                foreach (array_keys($GLOBALS['TL_DCA'][$filter->dataContainer]['fields']) as $field) {
                    if (!\in_array($field, $itemFields)) {
                        $item[$field] = null;
                    }
                }
            }

            $this->setItems($this->parseItems($items, $itemTemplate));
        }

        // render
        $listTemplate = $this->_manager->getListTemplateByName(($listTemplate ?: $listConfig->listTemplate) ?: 'default');
        $templateData = $this->jsonSerialize();

        $event = $this->_dispatcher->dispatch(ListBeforeRenderEvent::NAME, new ListBeforeRenderEvent($templateData, $this, $listConfig));

        $rendered = System::getContainer()->get('twig')->render($listTemplate, $event->getTemplateData());

        $event = $this->_dispatcher->dispatch(ListAfterRenderEvent::NAME, new ListAfterRenderEvent($rendered, $event->getTemplateData(), $this, $listConfig));

        return $event->getRendered();
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
        $event = $this->_dispatcher->dispatch(ListBeforeParseItemsEvent::NAME, new ListBeforeParseItemsEvent($items, $this, $listConfig));

        $items = $event->getItems() ?: [];

        foreach ($items as $item) {
            ++$count;
            $first = 1 == $count ? ' first' : '';
            $last = $count == $limit ? ' last' : '';
            $oddEven = (0 == ($count % 2)) ? ' even' : ' odd';

            $cssClass = 'item item_'.$count.$first.$last.$oddEven;

            if (null !== ($itemClass = $this->getItemClassByName($listConfig->item ?: 'default'))) {
                $reflection = new \ReflectionClass($itemClass);

                if (!$reflection->implementsInterface(ItemInterface::class)) {
                    throw new \Exception(sprintf('Item class %s must implement %s', $itemClass, ItemInterface::class));
                }

                if (!$reflection->implementsInterface(\JsonSerializable::class)) {
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

            $results[] = $parsedResult;
        }

        /** @var ListAfterParseItemsEvent $event */
        $event = $this->_dispatcher->dispatch(ListAfterParseItemsEvent::NAME, new ListAfterParseItemsEvent($items, $results, $this, $listConfig));

        return $event->getParsedItems();
    }

    public function applyListConfigToQueryBuilder(int $totalCount, FilterQueryBuilder $queryBuilder): void
    {
        $listConfig = $this->_manager->getListConfig();

        // offset
        $offset = (int) ($listConfig->skipFirst);

        // limit
        $limit = null;

        if ($listConfig->numberOfItems > 0) {
            $limit = $listConfig->numberOfItems;
        }

        $queryParts = $queryBuilder->getQueryParts();

        if (!empty($queryParts['orderBy'])) {
            list($offset, $limit) = $this->splitResults($offset, $totalCount, $limit);
            // split the results
            $queryBuilder->setFirstResult($offset)->setMaxResults($limit);

            return;
        }
        // sorting
        $currentSorting = $this->getCurrentSorting();

        if (ListConfig::SORTING_MODE_RANDOM == $currentSorting['order']) {
            $randomSeed = $this->_manager->getRequest()->getGet(RandomPagination::PARAM_RANDOM) ?: rand(1, 500);
            $queryBuilder->orderBy('RAND("'.(int) $randomSeed.'")');
            list($offset, $limit) = $this->splitResults($offset, $totalCount, $limit, $randomSeed);
        } elseif (ListConfig::SORTING_MODE_MANUAL == $currentSorting['order']) {
            $sortingItems = StringUtil::deserialize($listConfig->sortingItems, true);

            if (!empty($sortingItems)) {
                $queryBuilder->orderBy('FIELD(id,'.implode(',', $sortingItems).')', ' ');
            }

            list($offset, $limit) = $this->splitResults($offset, $totalCount, $limit);
        } else {
            if (!empty($currentSorting)) {
                $queryBuilder->orderBy($currentSorting['order'], $currentSorting['sort']);
            }

            list($offset, $limit) = $this->splitResults($offset, $totalCount, $limit);
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
            $page = $this->_manager->getRequest()->getGet($id) ?: 1;
            $this->setPage($page);

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($offsettedTotal / $listConfig->perPage), 1)) {
                global $objPage;
                $objPage->noSearch = 1;
                $objPage->cache = 0;

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

            // Add the pagination menu
            if ($listConfig->addAjaxPagination) {
                $pagination = new RandomPagination($randomSeed, $offsettedTotal, $listConfig->perPage, Config::get('maxPaginationLinks'), $id, new FrontendTemplate('pagination_list_ajax'));
            } else {
                $pagination = new RandomPagination($randomSeed, $offsettedTotal, $listConfig->perPage, Config::get('maxPaginationLinks'), $id);
            }

            $this->setPagination($pagination->generate("\n  "));
        }

        return [$offset, $limit];
    }

    /**
     * {@inheritdoc}
     */
    public function generateTableHeader(): array
    {
        $headerFields = [];
        $currentSorting = $this->getCurrentSorting();
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
            if ($data['eval']['addAsDataAttribute'] && $listConfig->{$field}) {
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

                die($shareUrl);
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
    public function getCurrentSorting(): array
    {
        $listConfig = $this->_manager->getListConfig();
        $filter = (object) $this->_manager->getFilterConfig();
        $request = $this->_manager->getRequest();
        $sortingAllowed = $listConfig->isTableList && $listConfig->hasHeader && $listConfig->sortingHeader;

        // GET parameter
        if ($sortingAllowed && ($orderField = $request->getGet('order')) && ($sort = $request->getGet('sort'))) {
            // anti sql injection: check if field exists
            /** @var Database $db */
            $db = $this->_manager->getFramework()->getAdapter(Database::class);

            if ($db->getInstance()->fieldExists($orderField, $filter->dataContainer)
                && \in_array($sort, ListConfig::SORTING_DIRECTIONS)) {
                $currentSorting = [
                    'order' => $request->getGet('order'),
                    'sort' => $request->getGet('sort'),
                ];
            } else {
                $currentSorting = [];
            }
        } // initial
        else {
            switch ($listConfig->sortingMode) {
                case ListConfig::SORTING_MODE_TEXT:
                    $currentSorting = [
                        'order' => $listConfig->sortingText,
                    ];

                    break;

                case ListConfig::SORTING_MODE_RANDOM:
                    $currentSorting = [
                        'order' => ListConfig::SORTING_MODE_RANDOM,
                    ];

                    break;

                case ListConfig::SORTING_MODE_MANUAL:
                    $currentSorting = [
                        'order' => ListConfig::SORTING_MODE_MANUAL,
                    ];

                    break;

                default:
                    $currentSorting = [
                        'order' => $listConfig->sortingField,
                        'sort' => $listConfig->sortingDirection,
                    ];

                    break;
            }
        }

        return $currentSorting;
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

    /**
     * @param string $wrapperId
     */
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

    /**
     * @param string $dataAttributes
     */
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

    /**
     * @param bool $showInitialResults
     */
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

    /**
     * @param bool $isSubmitted
     */
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

    /**
     * @param bool $showItemCount
     */
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

    /**
     * @param string $itemsFoundText
     */
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

    /**
     * @param array $items
     */
    public function setItems(array $items)
    {
        $this->_items = $items;
    }

    /**
     * @return string
     */
    public function getPagination(): ?string
    {
        return $this->_pagination;
    }

    /**
     * @param string $pagination
     */
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

    /**
     * @param bool $showNoItemsText
     */
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

    /**
     * @param string $noItemsText
     */
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

    /**
     * @param array $masonryStampContentElements
     */
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

    /**
     * @param array $header
     */
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

    /**
     * @param bool $sortingHeader
     */
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

        if (!empty($arrRoot) && !\in_array($this->getJumpTo(), $arrRoot)) {
            return $arrPages;
        }

        $filter = (object) $this->_manager->getFilterConfig()->getFilter();
        $listConfig = $this->_manager->getListConfig();

        /** @var FilterQueryBuilder $queryBuilder */
        $queryBuilder = $this->_manager->getFilterManager()->getQueryBuilder($filter->id);

        $fields = $filter->dataContainer.'.* ';

        if (($totalCount = $queryBuilder->select($fields)->execute()->rowCount()) < 1) {
            return $arrPages;
        }

        $this->_dispatcher->dispatch(ListModifyQueryBuilderEvent::NAME, new ListModifyQueryBuilderEvent($queryBuilder, $this, $listConfig));

        $items = $queryBuilder->execute()->fetchAll();

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

            $result->setIdOrAlias($idOrAlias);
            $result->addDetailsUrl($idOrAlias, $result, $listConfig, true);

            $url = $result->getDetailsUrl(false);

            if (null === $url || empty($url)) {
                continue;
            }

            if (\in_array($url, $arrPages)) {
                continue;
            }

            $arrPages[] = $url;
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
}

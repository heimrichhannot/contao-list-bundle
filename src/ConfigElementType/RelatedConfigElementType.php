<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ConfigElementType;

use Contao\Controller;
use Contao\Database;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ListBundle\DataContainer\ListConfigElementContainer;
use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\UtilsBundle\Util\Utils;

class RelatedConfigElementType implements ListConfigElementTypeInterface
{
    protected Utils $util;

    public function __construct(Utils $utils)
    {
        $this->util = $utils;
    }

    /**
     * Return the config element type alias.
     */
    public static function getType(): string
    {
        return 'related';
    }

    /**
     * Return the config element type palette.
     */
    public function getPalette(): string
    {
        return '{config_legend},relatedExplanation,relatedListModule,relatedCriteriaExplanation,relatedCriteria;';
    }

    /**
     * Update the item data.
     */
    public function addToListItemData(ListConfigElementData $configElementData): void
    {
        $listConfigElement = $configElementData->getListConfigElement();
        $item = $configElementData->getItem();

        $item->setFormattedValue(
            $listConfigElement->templateVariable ?: 'relatedItems',
            $this->renderRelated($listConfigElement, $item)
        );

        $configElementData->setItem($item);
    }

    protected function renderRelated(Model $configElement, ItemInterface $item): ?string
    {
        $GLOBALS['HUH_LIST_RELATED'] = [];

        $this->applyTagsFilter($configElement, $item);
        $this->applyCategoriesFilter($configElement, $item);

        $result = Controller::getFrontendModule($configElement->relatedListModule);

        unset($GLOBALS['HUH_LIST_RELATED']);

        return $result;
    }

    protected function applyTagsFilter(Model $configElement, ItemInterface $item): void
    {
        if (!class_exists('\Codefog\TagsBundle\CodefogTagsBundle') || !$configElement->tagsField) {
            return;
        }

        $table = $item->getDataContainer();

        $criteria = StringUtil::deserialize($configElement->relatedCriteria, true);

        if (empty($criteria)) {
            return;
        }

        if (!in_array(ListConfigElementContainer::RELATED_CRITERIUM_TAGS, $criteria)) {
            return;
        }

        if (empty($GLOBALS['TL_DCA'][$table])) {
            Controller::loadDataContainer($table);
        }

        $dca = $GLOBALS['TL_DCA'][$table]['fields'][$configElement->tagsField];

        $source = $dca['eval']['tagsManager'];
        $nonTlTable = str_starts_with($table, 'tl_') ? substr($table, 3) : $table;  # remove the "tl_" prefix
        $cfgTable = $dca['relation']['relationTable'] ?? 'tl_cfg_tag_'.$nonTlTable;

        $tagRecords = Database::getInstance()->prepare("SELECT t.id FROM tl_cfg_tag t INNER JOIN $cfgTable t2 ON t.id = t2.cfg_tag_id".
            " WHERE t2.{$nonTlTable}_id=? AND t.source=?")->execute(
            $item->getRawValue('id'),
            $source
        );

        if ($tagRecords->numRows < 1) {
            return;
        }

        $relatedIds = Database::getInstance()->prepare(
            "SELECT t.* FROM $cfgTable t WHERE t.cfg_tag_id IN (".implode(',', $tagRecords->fetchEach('id')).')'
        )->execute();

        if ($relatedIds->numRows < 1) {
            return;
        }

        $itemIds = $relatedIds->fetchEach($nonTlTable.'_id');

        // exclude the item itself
        $itemIds = array_diff($itemIds, [$item->getRawValue('id')]);

        $GLOBALS['HUH_LIST_RELATED'][ListConfigElementContainer::RELATED_CRITERIUM_TAGS] = [
            'itemIds' => $itemIds,
        ];
    }

    protected function applyCategoriesFilter(Model $configElement, ItemInterface $item): void
    {
        if (!class_exists('\HeimrichHannot\CategoriesBundle\CategoriesBundle') || !$configElement->categoriesField) {
            return;
        }

        $table = $item->getDataContainer();

        $criteria = StringUtil::deserialize($configElement->relatedCriteria, true);

        if (empty($criteria)) {
            return;
        }

        if (!in_array(ListConfigElementContainer::RELATED_CRITERIUM_CATEGORIES, $criteria)) {
            return;
        }

        $categories = System::getContainer()->get('huh.categories.manager')->findByEntityAndCategoryFieldAndTable(
            $item->getRawValue('id'), $configElement->categoriesField, $table
        );

        if (null !== $categories) {
            return;
        }

        $relatedIds = Database::getInstance()->prepare(
            'SELECT t.* FROM tl_category_association t WHERE t.category IN ('.implode(',', $categories->fetchEach('id')).')'
        )->execute();

        if ($relatedIds->numRows < 1) {
            return;
        }

        $itemIds = $relatedIds->fetchEach('entity');

        // exclude the item itself
        $itemIds = array_diff($itemIds, [$item->getRawValue('id')]);

        $GLOBALS['HUH_LIST_RELATED'][ListConfigElementContainer::RELATED_CRITERIUM_CATEGORIES] = [
            'itemIds' => $itemIds,
        ];
    }
}

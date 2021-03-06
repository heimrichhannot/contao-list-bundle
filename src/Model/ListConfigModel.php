<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Model;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property int    $dateAdded
 * @property string $title
 * @property int    $numberOfItems
 * @property int    $perPage
 * @property int    $skipFirst
 * @property bool   $doNotRenderEmpty
 * @property bool   $showItemCount
 * @property string $itemCountText
 * @property bool   $showNoItemsText
 * @property string $noItemsText
 * @property bool   $showInitialResults
 * @property bool   $isTableList
 * @property bool   $hasHeader
 * @property bool   $sortingHeader
 * @property int    $tableFields
 * @property int    $sortingMode
 * @property string $sortingField
 * @property string $sortingDirection
 * @property string $sortingText
 * @property string $sortingItems
 * @property bool   $useAlias
 * @property string $aliasField
 * @property bool   $useModal
 * @property bool   $addDetails
 * @property int    $jumpToDetails
 * @property bool   $addShare
 * @property int    $jumpToShare
 * @property bool   $shareAutoItem
 * @property bool   $addAjaxPagination
 * @property bool   $addInfiniteScroll
 * @property bool   $addMasonry
 * @property string $masonryStampContentElements
 * @property string $itemTemplate
 * @property string $listTemplate
 * @property string $itemChoiceTemplate
 * @property int    $filter
 * @property bool   $limitFormattedFields
 * @property string $formattedFields
 * @property string $list
 * @property string $manager
 * @property string $item
 * @property bool   $noSearch
 * @property bool   $doNotIndexItems
 * @property string $listContextVariables
 */
class ListConfigModel extends Model
{
    protected static $strTable = 'tl_list_config';
}

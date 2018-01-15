<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\ListBundle\Model;

/**
 * @property string $title
 * @property int    $numberOfItems
 * @property int    $perPage
 * @property int    $skipFirst
 * @property bool   $showItemCount
 * @property bool   $showInitialResults
 * @property bool   $isTableList
 * @property bool   $hasHeader
 * @property bool   $sortingHeader
 * @property int    $tableFields
 * @property int    $sortingMode
 * @property string $sortingField
 * @property string $sortingDirection
 * @property string $sortingText
 * @property int    $addDetails
 * @property int    $jumpToDetails
 * @property int    $addShare
 * @property int    $jumpToShare
 * @property int    $shareAutoItem
 * @property int    $addAjaxPagination
 * @property int    $addInfiniteScroll
 * @property int    $addMasonry
 * @property int    $masonryStampContentElements
 * @property string $itemTemplate
 * @property int    $filter
 */
class ListConfigModel extends \Model
{
    protected static $strTable = 'tl_list_config';
}

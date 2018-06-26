<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Lists;

use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\ListBundle\Manager\ListManagerInterface;

interface ListInterface
{
    /**
     * Get the list manager.
     *
     * @return ListManagerInterface
     */
    public function getManager(): ListManagerInterface;

    /**
     * Get the list config dataContainer name.
     *
     * @return string
     */
    public function getDataContainer(): ?string;

    /**
     * Get the current module data.
     *
     * @return array
     */
    public function getModule(): ?array;

    /**
     * Parse a given list.
     *
     * @param string|null $listTemplate
     * @param string|null $itemTemplate
     * @param array       $data
     *
     * @return string|null
     */
    public function parse(string $listTemplate = null, string $itemTemplate = null, array $data = []): ?string;

    /**
     * Returns a share url.
     *
     * @return mixed
     */
    public function handleShare();

    /**
     * Checks if a share token is empty or expired.
     *
     * @param $entity
     * @param $now
     *
     * @return mixed
     */
    public function shareTokenExpiredOrEmpty($entity, $now);

    /**
     * @return array
     */
    public function generateTableHeader(): ?array;

    /**
     * @return array
     */
    public function getCurrentSorting(): ?array;

    /**
     * Applies the list config to the query builder.
     *
     * @param int                $totalCount
     * @param FilterQueryBuilder $queryBuilder
     *
     * @return mixed
     */
    public function applyListConfigToQueryBuilder(int $totalCount, FilterQueryBuilder $queryBuilder): void;

    /**
     * @param      $offset
     * @param      $total
     * @param      $limit
     * @param null $randomSeed
     *
     * @return array|null
     */
    public function splitResults($offset, $total, $limit, $randomSeed = null): ?array;

    /**
     * @param array       $items
     * @param string|null $itemTemplate
     *
     * @return array
     */
    public function parseItems(array $items, string $itemTemplate = null): ?array;

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getItemClassByName(string $name);

    /**
     * @return string
     */
    public function getWrapperId(): ?string;

    /**
     * @param string $wrapperId
     */
    public function setWrapperId(string $wrapperId);

    /**
     * @return string
     */
    public function getDataAttributes(): ?string;

    /**
     * @param string $dataAttributes
     */
    public function setDataAttributes(string $dataAttributes);

    /**
     * @return bool
     */
    public function isShowInitialResults(): ?bool;

    /**
     * @param bool $showInitialResults
     */
    public function setShowInitialResults(bool $showInitialResults);

    /**
     * @return bool
     */
    public function isSubmitted(): ?bool;

    /**
     * @param bool $isSubmitted
     */
    public function setIsSubmitted(bool $isSubmitted);

    /**
     * @return bool
     */
    public function isShowItemCount(): ?bool;

    /**
     * @param bool $showItemCount
     */
    public function setShowItemCount(bool $showItemCount);

    /**
     * @return string
     */
    public function getItemsFoundText(): ?string;

    /**
     * @param string $itemsFoundText
     */
    public function setItemsFoundText(string $itemsFoundText);

    /**
     * @return array
     */
    public function getItems(): ?array;

    /**
     * @param array $items
     */
    public function setItems(array $items);

    /**
     * @return string
     */
    public function getPagination(): ?string;

    /**
     * @param string $pagination
     */
    public function setPagination(string $pagination);

    /**
     * @return bool
     */
    public function isShowNoItemsText(): ?bool;

    /**
     * @param bool $showNoItemsText
     */
    public function setShowNoItemsText(bool $showNoItemsText);

    /**
     * @return string
     */
    public function getNoItemsText(): ?string;

    /**
     * @param string $noItemsText
     */
    public function setNoItemsText(string $noItemsText);

    /**
     * @return array
     */
    public function getHeader(): ?array;

    /**
     * @param array $header
     */
    public function setHeader(array $header);

    /**
     * @return bool
     */
    public function isSortingHeader(): ?bool;

    /**
     * @param bool $sortingHeader
     */
    public function setSortingHeader(bool $sortingHeader);

    /**
     * Set current page of pagination.
     *
     * @param int $page
     */
    public function setPage(int $page): void;

    /**
     * Get current page of pagination.
     *
     * @return int
     */
    public function getPage(): int;

    /**
     * Get current details jumpTo page id.
     *
     * @return int
     */
    public function getJumpTo(): int;

    /**
     * Get list items as searchable pages.
     *
     * @param array $arrPages
     * @param int   $intRoot
     * @param bool  $blnIsSitemap
     *
     * @return array
     */
    public function getSearchablePages(array $arrPages, int $intRoot = 0, bool $blnIsSitemap = false): array;
}

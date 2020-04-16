<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Lists;

use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\ListBundle\Manager\ListManagerInterface;

interface ListInterface
{
    const DC_MULTILINGUAL_SUFFIX = '_dcm';

    /**
     * Get the list manager.
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
     * Applies the list config to the query builder.
     *
     * @return mixed
     */
    public function applyListConfigToQueryBuilder(int $totalCount, FilterQueryBuilder $queryBuilder): void;

    /**
     * @param      $offset
     * @param      $total
     * @param      $limit
     * @param null $randomSeed
     */
    public function splitResults($offset, $total, $limit, $randomSeed = null): ?array;

    /**
     * @return array
     */
    public function parseItems(array $items, string $itemTemplate = null): ?array;

    /**
     * @return mixed
     */
    public function getItemClassByName(string $name);

    /**
     * @return string
     */
    public function getWrapperId(): ?string;

    public function setWrapperId(string $wrapperId);

    /**
     * @return string
     */
    public function getDataAttributes(): ?string;

    public function setDataAttributes(string $dataAttributes);

    /**
     * @return bool
     */
    public function isShowInitialResults(): ?bool;

    public function setShowInitialResults(bool $showInitialResults);

    /**
     * @return bool
     */
    public function isSubmitted(): ?bool;

    public function setIsSubmitted(bool $isSubmitted);

    /**
     * @return bool
     */
    public function isShowItemCount(): ?bool;

    public function setShowItemCount(bool $showItemCount);

    /**
     * @return string
     */
    public function getItemsFoundText(): ?string;

    public function setItemsFoundText(string $itemsFoundText);

    /**
     * Get all items data.
     *
     * @return array
     */
    public function getRawItems(): ?array;

    /**
     * Set all items data.
     */
    public function setRawItems(array $items);

    /**
     * @return array
     */
    public function getItems(): ?array;

    public function setItems(array $items);

    /**
     * @return string
     */
    public function getPagination(): ?string;

    public function setPagination(string $pagination);

    /**
     * @return bool
     */
    public function isShowNoItemsText(): ?bool;

    public function setShowNoItemsText(bool $showNoItemsText);

    /**
     * @return string
     */
    public function getNoItemsText(): ?string;

    public function setNoItemsText(string $noItemsText);

    /**
     * @return array
     */
    public function getHeader(): ?array;

    public function setHeader(array $header);

    /**
     * @return bool
     */
    public function isSortingHeader(): ?bool;

    public function setSortingHeader(bool $sortingHeader);

    /**
     * Set current page of pagination.
     */
    public function setPage(int $page): void;

    /**
     * Get current page of pagination.
     */
    public function getPage(): int;

    /**
     * Get current details jumpTo page id.
     */
    public function getJumpTo(): int;

    /**
     * Get list items as searchable pages.
     */
    public function getSearchablePages(array $arrPages, int $intRoot = 0, bool $blnIsSitemap = false): array;

    public function setAddOverview(bool $addOverview);

    public function getAddOverview(): bool;

    /**
     * get jumpTo page for list overview.
     *
     * @return int
     */
    public function getJumpToOverview(): ?string;

    /**
     * set jumpTo page for list overview.
     */
    public function setJumpToOverview(string $jumpToOverview);
}

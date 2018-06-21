<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Item;

use HeimrichHannot\ListBundle\Manager\ListManagerInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;

interface ItemInterface
{
    /**
     * Parse the current item and return the parsed string.
     *
     * @return string The parsed item
     */
    public function parse(string $class = '', int $count = 0): string;

    /**
     * Get entire raw item data.
     *
     * @return array
     */
    public function getRaw(): array;

    /**
     * Set entire raw item data.
     *
     * @param array $data
     */
    public function setRaw(array $data = []): void;

    /**
     * Get raw value for a given property.
     *
     * @param string $name The property name
     *
     * @return mixed
     */
    public function getRawValue(string $name);

    /**
     * Set a raw value for a given property.
     *
     * @param string $name  The property name
     * @param mixed  $value The property value
     */
    public function setRawValue(string $name, $value): void;

    /**
     * Get the entire formatted data.
     *
     * @return array
     */
    public function getFormatted(): array;

    /**
     * Set entire formatted item data.
     *
     * @param array $data
     */
    public function setFormatted(array $data = []): void;

    /**
     * Get formatted value for a given property.
     *
     * @param string $name The property name
     *
     * @return mixed
     */
    public function getFormattedValue(string $name);

    /**
     * Set a formatted value for a given property.
     *
     * @param string $name  The property name
     * @param mixed  $value The property value
     */
    public function setFormattedValue(string $name, $value): void;

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
     * @param string $dataContainer
     */
    public function setDataContainer(string $dataContainer);

    /**
     * Get the current module data.
     *
     * @return array
     */
    public function getModule(): ?array;

    /**
     * Compute id (or alias) for a given item.
     *
     * @param ItemInterface   $item
     * @param ListConfigModel $listConfig
     *
     * @return string
     */
    public function generateIdOrAlias(self $item, ListConfigModel $listConfig): ?string;

    /**
     * Adds a details url to the item data.
     *
     * @param                 $idOrAlias
     * @param ItemInterface   $item
     * @param ListConfigModel $listConfig
     * @param bool            $absolute
     */
    public function addDetailsUrl($idOrAlias, self $item, ListConfigModel $listConfig, bool $absolute = false): void;

    /**
     * Adds a share url to the item data.
     *
     * @param ItemInterface   $item
     * @param ListConfigModel $listConfig
     */
    public function addShareUrl(self $item, ListConfigModel $listConfig): void;

    /**
     * @return string
     */
    public function getCssClass(): ?string;

    /**
     * @param string $cssClass
     */
    public function setCssClass(string $cssClass);

    /**
     * @return int
     */
    public function getCount(): ?int;

    /**
     * @param int $count
     */
    public function setCount(int $count);

    /**
     * @return string
     */
    public function getIdOrAlias(): ?string;

    /**
     * @param string $idOrAlias
     */
    public function setIdOrAlias(string $idOrAlias);

    /**
     * @return bool
     */
    public function isActive(): ?bool;

    /**
     * @param bool $active
     */
    public function setActive(bool $active);

    /**
     * @return bool
     */
    public function hasAddDetails();

    /**
     * @param bool $addDetails
     */
    public function setAddDetails(bool $addDetails);

    /**
     * @return bool
     */
    public function hasAddShare(): ?bool;

    /**
     * @param bool $addShare
     */
    public function setAddShare(bool $addShare);

    /**
     * @return bool
     */
    public function isUseModal();

    /**
     * @param bool $useModal
     */
    public function setUseModal(bool $useModal);

    /**
     * @return int
     */
    public function getJumpToDetails(): ?int;

    /**
     * @param int $jumpToDetails
     */
    public function setJumpToDetails(int $jumpToDetails);

    /**
     * @return string
     */
    public function getModalUrl(): ?string;

    /**
     * @param string $modalUrl
     */
    public function setModalUrl(string $modalUrl);

    /**
     * @return array
     */
    public function getTableFields();

    /**
     * @param array $tableFields
     */
    public function setTableFields(array $tableFields);

    /**
     * Get the details url.
     *
     * @param bool $external Determine if external urls should be returned as well (required by search index)
     *
     * @return null|string
     */
    public function getDetailsUrl(bool $external = true): ?string;
}

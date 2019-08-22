<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ConfigElementType;

interface ListConfigElementTypeInterface
{
    /**
     * Return the list config element type alias.
     *
     * @return string
     */
    public static function getType(): string;

    /**
     * Return the list config element type palette.
     *
     * @return string
     */
    public function getPalette(): string;

    /**
     * Update the item data.
     *
     * @param ListConfigElementData $configElementData
     */
    public function addToListItemData(ListConfigElementData $configElementData): void;
}

<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ConfigElementType;

/**
 * Interface ListConfigElementTypeInterface.
 *
 * @deprecated Use \HeimrichHannot\ConfigElementTypeBundle\ConfigElementType\ConfigElementTypeInterface instead
 */
interface ListConfigElementTypeInterface
{
    /**
     * Return the list config element type alias.
     */
    public static function getType(): string;

    /**
     * Return the list config element type palette.
     */
    public function getPalette(): string;

    /**
     * Update the item data.
     */
    public function addToListItemData(ListConfigElementData $configElementData): void;
}
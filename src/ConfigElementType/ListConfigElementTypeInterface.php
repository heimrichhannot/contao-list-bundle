<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
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
     * Return the list config element type palette
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
<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ConfigElementType;

use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;

/**
 * Class ListConfigElementData.
 *
 * @deprecated Use Use \HeimrichHannot\ConfigElementTypeBundle\ConfigElementType\ConfigElementTypeInterface with ConfigElementData instead
 */
class ListConfigElementData
{
    /**
     * @var ItemInterface
     */
    protected $item;

    /**
     * @var ListConfigElementModel
     */
    protected $listConfigElement;

    /**
     * ListConfigElementData constructor.
     */
    public function __construct(ItemInterface $item, ListConfigElementModel $listConfigElement)
    {
        $this->item = $item;
        $this->listConfigElement = $listConfigElement;
    }

    public function getItem(): ItemInterface
    {
        return $this->item;
    }

    public function setItem(ItemInterface $item): void
    {
        $this->item = $item;
    }

    public function getListConfigElement(): ListConfigElementModel
    {
        return $this->listConfigElement;
    }

    public function setListConfigElement(ListConfigElementModel $listConfigElement): void
    {
        $this->listConfigElement = $listConfigElement;
    }
}

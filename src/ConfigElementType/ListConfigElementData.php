<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ConfigElementType;

use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;

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
     *
     * @param ItemInterface          $item
     * @param ListConfigElementModel $listConfigElement
     */
    public function __construct(ItemInterface $item, ListConfigElementModel $listConfigElement)
    {
        $this->item = $item;
        $this->listConfigElement = $listConfigElement;
    }

    /**
     * @return ItemInterface
     */
    public function getItem(): ItemInterface
    {
        return $this->item;
    }

    /**
     * @param ItemInterface $item
     */
    public function setItem(ItemInterface $item): void
    {
        $this->item = $item;
    }

    /**
     * @return ListConfigElementModel
     */
    public function getListConfigElement(): ListConfigElementModel
    {
        return $this->listConfigElement;
    }

    /**
     * @param ListConfigElementModel $listConfigElement
     */
    public function setListConfigElement(ListConfigElementModel $listConfigElement): void
    {
        $this->listConfigElement = $listConfigElement;
    }
}
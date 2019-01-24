<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Event;

use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use Symfony\Component\EventDispatcher\Event;

class ListAfterParseItemsEvent extends Event
{
    const NAME = 'huh.list.event.list_after_parse_items';

    /**
     * @var array
     */
    protected $items;

    /**
     * @var array
     */
    protected $parsedItems;

    /**
     * @var ListInterface
     */
    protected $list;

    /**
     * @var ListConfigModel
     */
    protected $listConfig;

    /**
     * @param array           $items
     * @param array           $parsedItems
     * @param ListInterface   $list
     * @param ListConfigModel $listConfig
     */
    public function __construct(array $items, array $parsedItems, ListInterface $list, ListConfigModel $listConfig)
    {
        $this->items = $items;
        $this->parsedItems = $parsedItems;
        $this->list = $list;
        $this->listConfig = $listConfig;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @return array
     */
    public function getParsedItems(): array
    {
        return $this->parsedItems;
    }

    /**
     * @param array $parsedItems
     */
    public function setParsedItems(array $parsedItems): void
    {
        $this->parsedItems = $parsedItems;
    }

    /**
     * @return ListInterface
     */
    public function getList(): ListInterface
    {
        return $this->list;
    }

    /**
     * @param ListInterface $list
     */
    public function setList(ListInterface $list): void
    {
        $this->list = $list;
    }

    /**
     * @return ListConfigModel
     */
    public function getListConfig(): ListConfigModel
    {
        return $this->listConfig;
    }

    /**
     * @param ListConfigModel $listConfig
     */
    public function setListConfig(ListConfigModel $listConfig): void
    {
        $this->listConfig = $listConfig;
    }
}

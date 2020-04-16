<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
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

    public function __construct(array $items, array $parsedItems, ListInterface $list, ListConfigModel $listConfig)
    {
        $this->items = $items;
        $this->parsedItems = $parsedItems;
        $this->list = $list;
        $this->listConfig = $listConfig;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function getParsedItems(): array
    {
        return $this->parsedItems;
    }

    public function setParsedItems(array $parsedItems): void
    {
        $this->parsedItems = $parsedItems;
    }

    public function getList(): ListInterface
    {
        return $this->list;
    }

    public function setList(ListInterface $list): void
    {
        $this->list = $list;
    }

    public function getListConfig(): ListConfigModel
    {
        return $this->listConfig;
    }

    public function setListConfig(ListConfigModel $listConfig): void
    {
        $this->listConfig = $listConfig;
    }
}

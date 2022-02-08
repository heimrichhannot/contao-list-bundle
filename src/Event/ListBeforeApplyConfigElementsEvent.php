<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Event;

use Contao\Model\Collection;
use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use Symfony\Contracts\EventDispatcher\Event;

class ListBeforeApplyConfigElementsEvent extends Event
{
    /**
     * @var Collection|null
     */
    private $listConfigElements;
    /**
     * @var ListConfigModel
     */
    private $listConfig;
    /**
     * @var ItemInterface
     */
    private $item;

    public function __construct(?Collection $listConfigElements, ListConfigModel $listConfig, ItemInterface $item)
    {
        $this->listConfigElements = $listConfigElements;
        $this->listConfig = $listConfig;
        $this->item = $item;
    }

    public function getListConfig(): ListConfigModel
    {
        return $this->listConfig;
    }

    public function getItem(): ItemInterface
    {
        return $this->item;
    }

    public function getListConfigElements(): ?Collection
    {
        return $this->listConfigElements;
    }

    public function setListConfigElements(?Collection $listConfigElements): void
    {
        $this->listConfigElements = $listConfigElements;
    }
}

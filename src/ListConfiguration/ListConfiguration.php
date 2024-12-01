<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ListConfiguration;

use HeimrichHannot\ListBundle\Model\ListConfigModel;

class ListConfiguration
{
    private string $dataContainer;
    private ListConfigModel $listConfigModel;
    private int $maxItems = 0;
    private int $maxItemsPerPage = 0;

    public function __construct(string $dataContainer, ListConfigModel $listConfigModel)
    {
        $this->dataContainer = $dataContainer;
        $this->listConfigModel = $listConfigModel;
    }

    public function getDataContainer(): string
    {
        return $this->dataContainer;
    }

    public function getListConfigModel(): ListConfigModel
    {
        return $this->listConfigModel;
    }

    public function getMaxItems(): int
    {
        return $this->maxItems;
    }

    public function setMaxItems(int $maxItems): void
    {
        $this->maxItems = $maxItems;
    }

    public function getMaxItemsPerPage(): int
    {
        return $this->maxItemsPerPage;
    }

    public function setMaxItemsPerPage(int $maxItemsPerPage): void
    {
        $this->maxItemsPerPage = $maxItemsPerPage;
    }
}

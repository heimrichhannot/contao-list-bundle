<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ListConfiguration;

use HeimrichHannot\ListBundle\Filter\FilterConfiguration;
use HeimrichHannot\ListBundle\Model\ListConfigModel;

class ListConfiguration
{
    private string $dataContainer;
    private ListConfigModel $listConfigModel;
    protected FilterConfiguration $filterConfiguration;

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

    public function getFilterConfiguration(): FilterConfiguration
    {
        return $this->filterConfiguration;
    }
}

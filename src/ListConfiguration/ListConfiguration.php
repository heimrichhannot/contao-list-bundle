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
    /** @var string */
    private $dataContainer;

    /** @var ListConfigModel */
    private $listConfigModel;

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
}

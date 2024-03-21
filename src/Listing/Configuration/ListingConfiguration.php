<?php

namespace HeimrichHannot\ListBundle\Listing\Configuration;

use HeimrichHannot\ListBundle\Filter\FilterConfiguration;
use HeimrichHannot\ListBundle\Model\ListConfigModel;

class ListingConfiguration
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

    public function setFilterConfiguration(FilterConfiguration $filterConfiguration): ListingConfiguration
    {
        $this->filterConfiguration = $filterConfiguration;
        return $this;
    }

    public function getFilterConfiguration(): FilterConfiguration
    {
        return $this->filterConfiguration;
    }
}
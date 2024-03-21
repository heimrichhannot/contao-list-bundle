<?php

namespace HeimrichHannot\ListBundle\Filter;

use HeimrichHannot\FilterBundle\Model\FilterConfigModel;

class FilterConfiguration
{
    private string $dataContainer;
    private FilterConfigModel $filterConfigModel;

    public function __construct(string $dataContainer, FilterConfigModel $filterConfigModel)
    {
        $this->dataContainer = $dataContainer;
        $this->filterConfigModel = $filterConfigModel;
    }

    public function getDataContainer(): string
    {
        return $this->dataContainer;
    }

    public function setDataContainer(string $dataContainer): FilterConfiguration
    {
        $this->dataContainer = $dataContainer;
        return $this;
    }

    public function getFilterConfigModel(): FilterConfigModel
    {
        return $this->filterConfigModel;
    }

    public function setFilterConfigModel(FilterConfigModel $filterConfigModel): FilterConfiguration
    {
        $this->filterConfigModel = $filterConfigModel;
        return $this;
    }


}
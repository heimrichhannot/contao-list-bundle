<?php

namespace HeimrichHannot\ListBundle\Listing\Configuration;

use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\ListBundle\Filter\FilterConfiguration;
use HeimrichHannot\ListBundle\Model\ListConfigModel;

class ListConfigurationFactory
{
    public function createFromModel(ListConfigModel $model): ListingConfiguration
    {
        $filterModel = FilterConfigModel::findByPk($model->filter);
        $filterConfiguration = new FilterConfiguration($filterModel->dataContainer, $filterModel);

        $listingConfiguration = new ListingConfiguration($model::getTable(), new ListConfigModel());
    }
}
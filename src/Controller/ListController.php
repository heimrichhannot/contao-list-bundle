<?php

namespace HeimrichHannot\ListBundle\Controller;

use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\ListBundle\Configuration\ListConfiguration;

class ListController
{
    /**
     * @var FilterManager
     */
    private $filterManager;

    public function __construct(FilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }

    public function renderList(ListConfiguration $listConfiguration): string
    {
        $filterConfig = $this->filterManager->findById($listConfiguration->getFilter());
        $queryBuilder = $this->filterManager->getQueryBuilder($listConfiguration->getFilter());
        $isSubmitted = $filterConfig->hasData();

        $templateData = $this->prepareListTemplate();
        $templateData['isSubmitted'] = $isSubmitted;

        $totalCount = 0;
        if ($isSubmitted || $listConfiguration->getShowInitialResults()) {
            $countQueryBuilder = clone $queryBuilder;
            $countQueryBuilder->select('COUNT('.$filterConfig->getFilter()['dataContainer'].'.id)');
            $totalCount = (int)$countQueryBuilder->execute()->fetchOne();
        }

        return '';
    }

    private function prepareListTemplate(): array
    {
        $listTemplateData = [
        ];


        return $listTemplateData;
    }
}
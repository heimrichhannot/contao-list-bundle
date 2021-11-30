<?php

namespace HeimrichHannot\ListBundle\Controller;

use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\ListBundle\Configuration\ListConfiguration;
use HeimrichHannot\UtilsBundle\Util\AbstractServiceSubscriber;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Psr\Container\ContainerInterface;

class ListController extends AbstractServiceSubscriber
{
    /**
     * @var FilterManager
     */
    private $filterManager;
    /**
     * @var Utils
     */
    private $utils;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container, FilterManager $filterManager, Utils $utils)
    {
        $this->filterManager = $filterManager;
        $this->utils = $utils;
        $this->container = $container;
    }

    public function renderList(ListConfiguration $listConfiguration): string
    {
        if ($this->utils->container()->isDev()) {
            $stopwatch = $this->container->get('debug.stopwatch');
            $stopwatch->start('huh.list.render_list (ID '.$listConfiguration->getIdOrAlias().')');
        }

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

        $templateData['totalItemCount'] = $totalCount;

        $itemsQueryBuilder = clone $queryBuilder;
        $this->applyListConfigurationToQueryBuilder($listConfiguration, $itemsQueryBuilder, $totalCount);

        if (isset($stopwatch)) {
            $stopwatch->stop('huh.list.render_list (ID '.$listConfiguration->getIdOrAlias().')');
        }

        return '';
    }

    private function prepareListTemplate(): array
    {
        $listTemplateData = [
        ];


        return $listTemplateData;
    }

    private function applyListConfigurationToQueryBuilder(ListConfiguration $listConfiguration, FilterQueryBuilder $queryBuilder, int $totalCount)
    {

    }

    public static function getSubscribedServices()
    {
        return [
            'debug.stopwatch' => 'debug.stopwatch'
        ];
    }
}
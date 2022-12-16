<?php

namespace HeimrichHannot\ListBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Manager\ListManager;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;

/**
 * @Hook("getSearchablePages")
 */
class GetSearchablePagesListener
{
    private ListConfigRegistry   $listConfigRegistry;
    private ListManager $manager;
    private FilterManager        $filterManager;

    public function __construct(ListConfigRegistry $listConfigRegistry, ListManager $manager, FilterManager $filterManager)
    {
        $this->listConfigRegistry = $listConfigRegistry;
        $this->manager = $manager;
        $this->filterManager = $filterManager;
    }

    public function __invoke(array $pages, int $rootId = null, bool $isSitemap = false, string $language = null): array
    {
        if (null === ($listConfigs = $this->listConfigRegistry->findAll())) {
            return $pages;
        }

        foreach ($listConfigs as $listConfig) {
            $listConfig = $this->listConfigRegistry->computeListConfig((int) $listConfig->id);

            if ($listConfig->doNotIndexItems) {
                continue;
            }

            if (null !== ($listClass = $this->manager->getListByName($listConfig->list ?: 'default'))) {
                $reflection = new \ReflectionClass($listClass);

                if (!$reflection->implementsInterface(ListInterface::class)) {
                    throw new \Exception(sprintf('Item class %s must implement %s', $listClass, ListInterface::class));
                }

                $this->manager->setListConfig($listConfig);
                $filter = $this->filterManager->findById($listConfig->filter);

                if (null === $filter) {
                    continue;
                }

                $filter = $filter->getFilter();

                if (empty($filter) || !isset($filter['dataContainer']) || null === $filter['dataContainer']) {
                    continue;
                }

                if ('tl_news' === $filter['dataContainer'] || 'tl_calendar_events' === $filter['dataContainer'] || 'tl_comments' === $filter['dataContainer'] || 'tl_faq' === $filter['dataContainer']) {
                    continue;
                }

                $this->manager->setList(new $listClass($this->manager));
                $pages = $this->manager->getList()->getSearchablePages($pages, $rootId, $isSitemap);
            }
        }

        return $pages;
    }
}
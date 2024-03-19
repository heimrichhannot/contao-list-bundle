<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use Exception;
use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Manager\ListManagerInterface;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;
use ReflectionClass;

class SearchListener
{
    private ContaoFramework $framework;
    private ListConfigRegistry $listConfigRegistry;
    private ListManagerInterface $manager;

    public function __construct(
        ContaoFramework $framework,
        ListConfigRegistry $listConfigRegistry,
        ListManagerInterface $manager
    ) {
        $this->framework = $framework;
        $this->listConfigRegistry = $listConfigRegistry;
        $this->manager = $manager;
    }

    /**
     * Add list items as searchable pages.
     *
     * @throws \ReflectionException
     */
    public function getSearchablePages(array $arrPages, $intRoot = 0, bool $blnIsSitemap = false): array
    {
        if (null === ($listConfigs = $this->listConfigRegistry->findAll())) {
            return $arrPages;
        }

        foreach ($listConfigs as $listConfig) {
            $listConfig = $this->listConfigRegistry->computeListConfig((int) $listConfig->id);

            if ($listConfig->doNotIndexItems) {
                continue;
            }

            if (null !== ($listClass = $this->manager->getListByName($listConfig->list ?: 'default'))) {
                $reflection = new ReflectionClass($listClass);

                if (!$reflection->implementsInterface(ListInterface::class)) {
                    throw new Exception(sprintf('Item class %s must implement %s', $listClass, ListInterface::class));
                }

                $this->manager->setListConfig($listConfig);
                $filter = System::getContainer()->get('huh.filter.manager')->findById($listConfig->filter);

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
                $arrPages = $this->manager->getList()->getSearchablePages($arrPages, $intRoot, $blnIsSitemap);
            }
        }

        return $arrPages;
    }
}
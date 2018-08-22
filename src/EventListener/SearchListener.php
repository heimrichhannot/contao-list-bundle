<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Manager\ListManagerInterface;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;

class SearchListener
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var ListConfigRegistry
     */
    private $listConfigRegistry;

    /**
     * @var ListManagerInterface
     */
    private $manager;

    public function __construct(ContaoFrameworkInterface $framework, ListConfigRegistry $listConfigRegistry, ListManagerInterface $manager)
    {
        $this->framework = $framework;
        $this->listConfigRegistry = $listConfigRegistry;
        $this->manager = $manager;
    }

    /**
     * Add list items as searchable pages.
     *
     * @param array $arrPages
     * @param int   $intRoot
     * @param bool  $blnIsSitemap
     *
     * @return array
     */
    public function getSearchablePages(array $arrPages, int $intRoot = 0, bool $blnIsSitemap = false): array
    {
        if (null === ($listConfigs = $this->listConfigRegistry->findAll())) {
            return $arrPages;
        }

        foreach ($listConfigs as $listConfig) {
            if (null !== ($listClass = $this->manager->getListByName($listConfig->list ?: 'default'))) {
                $reflection = new \ReflectionClass($listClass);

                if (!$reflection->implementsInterface(ListInterface::class)) {
                    throw new \Exception(sprintf('Item class %s must implement %s', $listClass, ListInterface::class));
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

                if ('tl_news' === $filter['dataContainer'] || 'tl_calendar' === $filter['dataContainer'] || 'tl_comments' === $filter['dataContainer'] || 'tl_faq' === $filter['dataContainer']) {
                    continue;
                }

                $this->manager->setList(new $listClass($this->manager));
                $arrPages = $this->manager->getList()->getSearchablePages($arrPages, $intRoot, $blnIsSitemap);
            }
        }

        return $arrPages;
    }
}

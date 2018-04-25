<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Event;

use Doctrine\DBAL\Query\QueryBuilder;
use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use Symfony\Component\EventDispatcher\Event;

class ListModifyQueryBuilderEvent extends Event
{
    const NAME = 'huh.list.event.list_modify_query_builder';

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var ListInterface
     */
    protected $list;

    /**
     * @var ListConfigModel
     */
    protected $listConfig;

    /**
     * @param QueryBuilder    $queryBuilder
     * @param ListInterface   $list
     * @param ListConfigModel $listConfig
     */
    public function __construct(QueryBuilder $queryBuilder, ListInterface $list, ListConfigModel $listConfig)
    {
        $this->queryBuilder = $queryBuilder;
        $this->list = $list;
        $this->listConfig = $listConfig;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder): void
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @return ListInterface
     */
    public function getList(): ListInterface
    {
        return $this->list;
    }

    /**
     * @param ListInterface $list
     */
    public function setList(ListInterface $list): void
    {
        $this->list = $list;
    }

    /**
     * @return ListConfigModel
     */
    public function getListConfig(): ListConfigModel
    {
        return $this->listConfig;
    }

    /**
     * @param ListConfigModel $listConfig
     */
    public function setListConfig(ListConfigModel $listConfig): void
    {
        $this->listConfig = $listConfig;
    }
}

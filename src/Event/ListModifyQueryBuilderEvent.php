<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
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
     * @var string
     */
    private $fields;

    public function __construct(QueryBuilder $queryBuilder, ListInterface $list, ListConfigModel $listConfig, string $fields)
    {
        $this->queryBuilder = $queryBuilder;
        $this->list = $list;
        $this->listConfig = $listConfig;
        $this->fields = $fields;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function setQueryBuilder(QueryBuilder $queryBuilder): void
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function getList(): ListInterface
    {
        return $this->list;
    }

    public function setList(ListInterface $list): void
    {
        $this->list = $list;
    }

    public function getListConfig(): ListConfigModel
    {
        return $this->listConfig;
    }

    public function setListConfig(ListConfigModel $listConfig): void
    {
        $this->listConfig = $listConfig;
    }

    public function getFields(): string
    {
        return $this->fields;
    }

    public function setFields(string $fields): void
    {
        $this->fields = $fields;
    }
}

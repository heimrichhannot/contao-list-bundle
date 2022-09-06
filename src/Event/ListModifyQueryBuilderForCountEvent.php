<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Event;

use Doctrine\DBAL\Query\QueryBuilder;
use HeimrichHannot\ListBundle\ListConfiguration\ListConfiguration;
use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use Symfony\Contracts\EventDispatcher\Event;

class ListModifyQueryBuilderForCountEvent extends Event
{
    const NAME = 'huh.list.event.list_modify_query_builder_for_count';

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
    /** @var ListConfiguration */
    private $listConfiguration;

    public function __construct(QueryBuilder $queryBuilder, ListInterface $list, ListConfigModel $listConfig, string $fields, ListConfiguration $listConfiguration)
    {
        $this->queryBuilder = $queryBuilder;
        $this->list = $list;
        $this->listConfig = $listConfig;
        $this->fields = $fields;
        $this->listConfiguration = $listConfiguration;
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

    /**
     * @deprecated Use ListConfiguration
     */
    public function getListConfig(): ListConfigModel
    {
        return $this->listConfig;
    }

    /**
     * @deprecated Use ListConfiguration
     */
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

    public function getListConfiguration(): ListConfiguration
    {
        return $this->listConfiguration;
    }

    public function setListConfiguration(ListConfiguration $listConfiguration): void
    {
        $this->listConfiguration = $listConfiguration;
    }
}

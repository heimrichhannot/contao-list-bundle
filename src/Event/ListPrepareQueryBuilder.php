<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Event;

use Doctrine\DBAL\Query\QueryBuilder;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use Symfony\Contracts\EventDispatcher\Event;

class ListPrepareQueryBuilder extends Event
{
    /**
     * @var QueryBuilder
     */
    private $queryBuilder;
    /**
     * @var array
     */
    private $databaseFields;

    /** @var array */
    private $queryBuilderFields = [];
    /**
     * @var ListConfigModel
     */
    private $listConfigModel;

    public function __construct(QueryBuilder $queryBuilder, array $databaseFields, ListConfigModel $listConfigModel)
    {
        $this->queryBuilder = $queryBuilder;
        $this->databaseFields = $databaseFields;
        $this->listConfigModel = $listConfigModel;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function getDatabaseFields(): array
    {
        return $this->databaseFields;
    }

    public function getQueryBuilderFields(): array
    {
        return $this->queryBuilderFields;
    }

    public function setQueryBuilderFields(array $queryBuilderFields): void
    {
        $this->queryBuilderFields = $queryBuilderFields;
    }

    public function getListConfigModel(): ListConfigModel
    {
        return $this->listConfigModel;
    }
}

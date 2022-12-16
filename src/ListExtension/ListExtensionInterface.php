<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ListExtension;

use Doctrine\DBAL\Query\QueryBuilder;
use HeimrichHannot\ListBundle\ListConfiguration\ListConfiguration;

/**
 * @internal Spezification not final, no bc promise!
 */
interface ListExtensionInterface
{
    /**
     * Return a unique alias.
     */
    public static function getAlias(): string;

    /**
     * Check for preconditions to use this extension.
     * This method is executed at compile time, so no service container is available.
     * If there are no conditions, just return true.
     */
    public static function isEnabled(): bool;

    /**
     * Return fields that should be shown if the list extension is activated.
     * Return a empty array if the list extension has no additional configuration.
     */
    public static function getFields(): array;

    public function prepareQueryBuilder(QueryBuilder $queryBuilder, ListConfiguration $listConfiguration): void;

    public function prepareListTemplate(array &$templateData, ListConfiguration $listConfiguration): void;

    public function prepareListItemTemplate(array &$templateData, ListConfiguration $listConfiguration): void;
}

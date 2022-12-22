<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ListExtension;

use HeimrichHannot\ListBundle\Event\ListModifyQueryBuilderForCountEvent;
use HeimrichHannot\ListBundle\ListConfiguration\ListConfiguration;

abstract class AbstractListExtension implements ListExtensionInterface
{
    public static function getActivationFieldName(): string
    {
        return 'use'.ucfirst(static::getAlias());
    }

    public static function getFields(): array
    {
        return [];
    }

    public static function isEnabled(): bool
    {
        return true;
    }

    public function isEnabledInCurrentContext(ListConfiguration $listConfiguration): bool
    {
        return (bool) $listConfiguration->getListConfigModel()->{static::getActivationFieldName()};
    }

    public function onListModifyQueryBuilderForCountEvent(ListModifyQueryBuilderForCountEvent $event): void
    {
        // Override this method if needed
    }
}

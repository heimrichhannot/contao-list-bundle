<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ListExtension;

class ListExtensionCollection
{
    private array $collection = [];

    public function addExtension(ListExtensionInterface $extension): void
    {
        $this->collection[$extension::getAlias()] = $extension;
    }

    public function getExtension(string $type): ?ListExtensionInterface
    {
        return $this->collection[$type] ?? null;
    }

    /**
     * @return ListExtensionInterface[]|array
     */
    public function getExtensions(): array
    {
        return $this->collection;
    }

    /**
     * @return string[]|array
     */
    public function getRegisteredExtensionTypes(): array
    {
        return array_keys($this->collection);
    }
}
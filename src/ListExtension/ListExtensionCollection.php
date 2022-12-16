<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ListExtension;

class ListExtensionCollection
{
    /** @var array|ListExtensionInterface[] */
    private $collection = [];

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
     * @param array $context Typical $model->row()
     *
     * @return ListExtensionInterface[]|array
     */
    public function getEnabledExtensionsForContext(array $context): array
    {
        $extensions = [];

        foreach ($this->collection as $extension) {
            $fieldName = 'use'.ucfirst($extension::getAlias());

            if (isset($context[$fieldName]) && (bool) $context[$fieldName]) {
                $extensions[] = $extension;
            }
        }

        return $extensions;
    }

    /**
     * @return string[]|array
     */
    public function getRegisteredExtensionTypes(): array
    {
        return array_keys($this->collection);
    }
}

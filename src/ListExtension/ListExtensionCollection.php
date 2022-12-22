<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ListExtension;

use HeimrichHannot\ListBundle\ListConfiguration\ListConfiguration;

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
    public function getEnabledExtensionsForContext(ListConfiguration $listConfiguration): array
    {
        $extensions = [];

        foreach ($this->collection as $extension) {
            if ($extension->isEnabledInCurrentContext($listConfiguration)) {
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

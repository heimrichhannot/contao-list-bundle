<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\DependencyInjection\Compiler;

use HeimrichHannot\ListBundle\ListExtension\ListExtensionCollection;
use HeimrichHannot\ListBundle\ListExtension\ListExtensionInterface;
use HeimrichHannot\ListBundle\Registry\ListConfigElementRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ListCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->has(ListConfigElementRegistry::class)) {
            $definition = $container->findDefinition(ListConfigElementRegistry::class);
            $taggedServices = $container->findTaggedServiceIds('huh.list.config_element_type');

            foreach ($taggedServices as $id => $tags) {
                $definition->addMethodCall('addListConfigElementType', [new Reference($id)]);
            }
        }

        if ($container->has(ListExtensionCollection::class)) {
            $definition = $container->findDefinition(ListExtensionCollection::class);
            $taggedServices = $container->findTaggedServiceIds('huh.list.list_extension');

            foreach ($taggedServices as $id => $tags) {
                if (!class_implements($id, ListExtensionInterface::class)) {
                    continue;
                }

                if (!$id::isEnabled()) {
                    $container->removeDefinition($id);

                    continue;
                }

                $definition->addMethodCall('addExtension', [new Reference($id)]);
            }
        }
    }
}

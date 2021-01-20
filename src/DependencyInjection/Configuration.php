<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @var bool
     */
    private $debug;

    /**
     * Constructor.
     *
     * @param bool $debug
     */
    public function __construct($debug)
    {
        $this->debug = (bool) $debug;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('huh');

        $rootNode
            ->children()
                ->arrayNode('list')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('managers')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('id')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('items')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('class')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('lists')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('class')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('config_element_types')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('class')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('templates')
                        ->addDefaultsIfNotSet()
                                ->children()
                                    ->arrayNode('list')
                                    ->arrayPrototype()
                                        ->children()
                                            ->scalarNode('name')
                                                ->isRequired()
                                                ->cannotBeEmpty()
                                            ->end()
                                            ->scalarNode('template')
                                                ->isRequired()
                                                ->cannotBeEmpty()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('item')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('name')
                                            ->isRequired()
                                            ->cannotBeEmpty()
                                        ->end()
                                        ->scalarNode('template')
                                            ->isRequired()
                                            ->cannotBeEmpty()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('item_choice')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('name')
                                            ->isRequired()
                                            ->cannotBeEmpty()
                                        ->end()
                                        ->scalarNode('template')
                                            ->isRequired()
                                            ->cannotBeEmpty()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('list_prefixes')
                                ->prototype('scalar')
                            ->end()->end()
                            ->arrayNode('item_prefixes')
                                ->prototype('scalar')
                            ->end()->end()
                            ->arrayNode('item_choice_prefixes')
                                ->prototype('scalar')
                            ->end()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

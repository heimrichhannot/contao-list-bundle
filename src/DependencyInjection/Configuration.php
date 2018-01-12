<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
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
                        ->arrayNode('templates')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('list_item')->cannotBeEmpty()->defaultValue('@HeimrichHannotContaoListBundle/list_item.html.twig')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

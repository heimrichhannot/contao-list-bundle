<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\ListBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ListExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration(true);
        $processedConfig = $this->processConfiguration($configuration, $configs);

        $container->setParameter('huh.list', $processedConfig);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

//        $loader->load('config.yml');
//        $loader->load('services.yml');
    }
}

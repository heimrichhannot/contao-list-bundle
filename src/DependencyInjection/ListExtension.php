<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\DependencyInjection;

use HeimrichHannot\ListBundle\ListExtension\ListExtensionInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ListExtension extends Extension
{
    public function getAlias()
    {
        return 'huh_list';
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration(true);
        $processedConfig = $this->processConfiguration($configuration, $configs);

        $container->setParameter('huh.list', $processedConfig);
        $container->setParameter('huh_list', isset($processedConfig['list']) ? $processedConfig['list'] : []);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yml');

        $container->registerForAutoconfiguration(ListExtensionInterface::class)
            ->addTag('huh.list.list_extension');
    }
}

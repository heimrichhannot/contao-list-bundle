<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use HeimrichHannot\FilterBundle\HeimrichHannotContaoFilterBundle;
use HeimrichHannot\ListBundle\HeimrichHannotContaoListBundle;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use Symfony\Component\Config\Loader\LoaderInterface;

class Plugin implements BundlePluginInterface, ConfigPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(HeimrichHannotContaoListBundle::class)->setLoadAfter([ContaoCoreBundle::class, HeimrichHannotContaoFilterBundle::class]),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig)
    {
        $loader->load('@HeimrichHannotContaoListBundle/Resources/config/commands.yml');
    }

    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container)
    {
        $extensionConfigs = ContainerUtil::mergeConfigFile(
            'huh_list',
            $extensionName,
            $extensionConfigs,
            __DIR__.'/../Resources/config/config.yml'
        );

        return $extensionConfigs;
    }
}

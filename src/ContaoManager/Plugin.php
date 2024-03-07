<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
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
use Contao\ManagerPlugin\Config\ExtensionPluginInterface;
use Exception;
use HeimrichHannot\FilterBundle\HeimrichHannotContaoFilterBundle;
use HeimrichHannot\ListBundle\HeimrichHannotContaoListBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Yaml\Yaml;

class Plugin implements BundlePluginInterface, ExtensionPluginInterface, ConfigPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(HeimrichHannotContaoListBundle::class)->setLoadAfter([ContaoCoreBundle::class, HeimrichHannotContaoFilterBundle::class]),
        ];
    }

    /**
     * @throws Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig): void
    {
        $loader->load('@HeimrichHannotContaoListBundle/Resources/config/commands.yml');
    }

    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container): array
    {
        # todo: is this available in utils?
        $mergeConfigFile = function (
            string $activeExtensionName,
            string $extensionName,
            array $extensionConfigs,
            string $configFile
        ): array {
            if ($activeExtensionName === $extensionName && file_exists($configFile))
            {
                $config = Yaml::parseFile($configFile);
                $extensionConfigs = array_merge_recursive($extensionConfigs, is_array($config) ? $config : []);
            }
            return $extensionConfigs;
        };

        return $mergeConfigFile(
            'huh_list',
            $extensionName,
            $extensionConfigs,
            __DIR__.'/../Resources/config/config.yml'
        );
    }
}

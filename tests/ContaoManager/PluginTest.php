<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Test\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Parser\DelegatingParser;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\ManagerPlugin\PluginLoader;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ListBundle\ContaoManager\Plugin;
use HeimrichHannot\ListBundle\HeimrichHannotContaoListBundle;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount;

class PluginTest extends ContaoTestCase
{
    public function testInstantiation()
    {
        $this->assertInstanceOf(Plugin::class, new Plugin());
    }

    public function testGetBundles()
    {
        $plugin = new Plugin();
        $bundles = $plugin->getBundles(new DelegatingParser());
        $this->assertCount(1, $bundles);
        $this->assertSame(HeimrichHannotContaoListBundle::class, $bundles[0]->getName());
        $this->assertSame(ContaoCoreBundle::class, $bundles[0]->getLoadAfter()[0]);
    }

    public function testGetExtensionConfig()
    {
        $plugin = new Plugin();
        $container = new ContainerBuilder($this->mockPluginLoader($this->never()), []);

        $extensionConfigs = $plugin->getExtensionConfig('huh_list', [[]], $container);
        $this->assertNotEmpty($extensionConfigs);
        $this->assertArrayHasKey('huh', $extensionConfigs);
        $this->assertArrayHasKey('list', $extensionConfigs['huh']);

        $extensionConfigs = $plugin->getExtensionConfig('huh_encore', [[]], $container);
        $this->assertNotEmpty($extensionConfigs);
        $this->assertArrayHasKey('huh', $extensionConfigs);
        $this->assertArrayHasKey('encore', $extensionConfigs['huh']);
    }

    /**
     * Mocks the plugin loader.
     *
     * @return PluginLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockPluginLoader(InvokedCount $expects, array $plugins = [])
    {
        $pluginLoader = $this->createMock(PluginLoader::class);

        $pluginLoader->expects($expects)->method('getInstancesOf')->with(PluginLoader::EXTENSION_PLUGINS)->willReturn($plugins);

        return $pluginLoader;
    }
}

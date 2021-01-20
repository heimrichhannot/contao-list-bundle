<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Test\DependenyInjection;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ListBundle\DependencyInjection\ListExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class ListExtensionTest extends ContaoTestCase
{
    public function testGetAlias()
    {
        $extension = new ListExtension();
        $this->assertSame('huh_list', $extension->getAlias());
    }

    public function testLoad()
    {
        $extension = new ListExtension();
        $container = new ContainerBuilder(new ParameterBag(['kernel.debug' => false]));

        $extension->load([], $container);

        $this->assertTrue($container->hasDefinition('huh.list.datacontainer.module'));
        $this->assertTrue($container->hasDefinition('huh.list.util.manager'));
        $this->assertTrue($container->hasDefinition('huh.list.list-config-registry'));
        $this->assertTrue($container->hasDefinition('huh.list.listener.search'));
    }
}

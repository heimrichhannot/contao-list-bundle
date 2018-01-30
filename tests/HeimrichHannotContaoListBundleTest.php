<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Tests;

use HeimrichHannot\ListBundle\DependencyInjection\ListExtension;
use HeimrichHannot\ListBundle\HeimrichHannotContaoListBundle;
use PHPUnit\Framework\TestCase;

class HeimrichHannotContaoListBundleTest extends TestCase
{
    public function testCanBeInstantiated()
    {
        $bundle = new HeimrichHannotContaoListBundle();
        $this->assertInstanceOf(HeimrichHannotContaoListBundle::class, $bundle);
    }

    public function testGetTheContainerExtension()
    {
        $bundle = new HeimrichHannotContaoListBundle();
        $this->assertInstanceOf(ListExtension::class, $bundle->getContainerExtension());
    }
}

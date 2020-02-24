<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Test\DependenyInjection;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ListBundle\DependencyInjection\Configuration;

class ConfigurationTest extends ContaoTestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration(false);

        $tree = $configuration->getConfigTreeBuilder();

        $root = $tree->buildTree();

        $this->assertSame('huh', $root->getName());

        $level1 = $root->getChildren();

        $this->assertCount(1, $level1);

        $listNode = $level1['list'];

        $level2 = $listNode->getChildren();

        $this->assertCount(5, $level2);
    }
}

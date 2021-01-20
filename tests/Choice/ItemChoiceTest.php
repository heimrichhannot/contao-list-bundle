<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Test\Choice;

use Codefog\TagsBundle\Manager\DefaultManager;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ListBundle\Choice\ItemChoice;

class ItemChoiceTest extends ContaoTestCase
{
    public function testCollect()
    {
        $container = $this->mockContainer();
        $container->setParameter('huh.list', []);
        System::setContainer($container);

        /** @var ItemChoice|\PHPUnit_Framework_MockObject_MockObject $choice */
        $choice = $this->getMockBuilder(ItemChoice::class)->disableOriginalConstructor()->setMethods(null)->getMock();

        $this->assertEmpty($choice->getChoices());

        System::getContainer()->setParameter('huh.list', [
            'list' => [
                'items' => [
                    ['name' => 'default', 'class' => DefaultManager::class],
                    ['name' => 'another', 'class' => '\Vendor\Project\AnotherManager'],
                ],
            ],
        ]);

        $this->assertSame([
            'default' => DefaultManager::class,
            'another' => '\Vendor\Project\AnotherManager',
        ], $choice->getChoices());
    }
}

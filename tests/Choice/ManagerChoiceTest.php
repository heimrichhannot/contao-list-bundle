<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Test\Choice;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ListBundle\Choice\ListChoice;
use HeimrichHannot\ListBundle\Choice\ManagerChoice;

class ManagerChoiceTest extends ContaoTestCase
{
    public function testCollect()
    {
        $container = $this->mockContainer();
        $container->setParameter('huh.list', []);
        System::setContainer($container);

        /** @var ListChoice|\PHPUnit_Framework_MockObject_MockObject $choice */
        $choice = $this->getMockBuilder(ManagerChoice::class)->disableOriginalConstructor()->setMethods(null)->getMock();

        $this->assertEmpty($choice->getChoices());

        System::getContainer()->setParameter('huh.list', [
            'list' => [
                'managers' => [
                    ['name' => 'default', 'id' => 'huh.list.manager.list'],
                    ['name' => 'another', 'id' => 'huh.list.manager.another'],
                ],
            ],
        ]);

        $this->assertSame([
            'another' => 'huh.list.manager.another',
            'default' => 'huh.list.manager.list',
        ], $choice->getChoices());
    }
}

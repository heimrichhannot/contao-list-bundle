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
use HeimrichHannot\ListBundle\Lists\DefaultList;

class ListChoiceTest extends ContaoTestCase
{
    public function testCollect()
    {
        $container = $this->mockContainer();
        $container->setParameter('huh.list', []);
        System::setContainer($container);

        /** @var ListChoice|\PHPUnit_Framework_MockObject_MockObject $choice */
        $choice = $this->getMockBuilder(ListChoice::class)->disableOriginalConstructor()->setMethods(null)->getMock();

        $this->assertEmpty($choice->getChoices());

        System::getContainer()->setParameter('huh.list', [
            'list' => [
                'lists' => [
                    ['name' => 'default', 'class' => DefaultList::class],
                    ['name' => 'another', 'class' => '\Vendor\Project\AnotherList'],
                ],
            ],
        ]);

        $this->assertSame([
            'default' => DefaultList::class,
            'another' => '\Vendor\Project\AnotherList',
        ], $choice->getChoices());
    }
}

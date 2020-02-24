<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Test\Choice;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ListBundle\Choice\ListItemChoiceTemplateChoice;
use HeimrichHannot\ListBundle\Choice\ListTemplateChoice;
use HeimrichHannot\UtilsBundle\Choice\TwigTemplateChoice;

class ListTemplateChoiceTest extends ContaoTestCase
{
    public function testCollect()
    {
        $twigTemplateChoice = $this->createMock(TwigTemplateChoice::class);
        $twigTemplateChoice->method('setContext')->willReturnSelf();
        $twigTemplateChoice->method('getCachedChoices')->willReturnOnConsecutiveCalls(
            ['check' => 'checkTemplate'],
            ['check' => 'checkTemplate', 'another' => 'anotherTemplate'],
            ['check' => 'checkTemplate', 'another' => 'anotherTemplate (Yaml)'],
            ['check' => 'checkTemplate', 'unset' => 'anotherTemplate (Yaml)']
        );

        $container = $this->mockContainer();
        $container->setParameter('huh.list', []);
        $container->set('huh.utils.choice.twig_template', $twigTemplateChoice);
        System::setContainer($container);

        /** @var ListItemChoiceTemplateChoice|\PHPUnit_Framework_MockObject_MockObject $choice */
        $choice = $this->getMockBuilder(ListTemplateChoice::class)->disableOriginalConstructor()->setMethods(null)->getMock();

        $this->assertEmpty($choice->getChoices());

        System::getContainer()->setParameter('huh.list', [
            'list' => [
                'templates' => [
                    'list' => [
                        ['name' => 'default', 'template' => 'defaultTemplate'],
                        ['name' => 'another', 'template' => 'anotherTemplate'],
                    ],
                ],
            ],
        ]);
        $this->assertSame([
            'another' => 'anotherTemplate (Yaml)',
            'default' => 'defaultTemplate (Yaml)',
        ], $choice->getChoices());

        System::getContainer()->setParameter('huh.list', [
            'list' => [
                'templates' => [
                    'list_prefixes' => [],
                    'list' => [
                        ['name' => 'default', 'template' => 'defaultTemplate'],
                        ['name' => 'another', 'template' => 'anotherTemplate'],
                    ],
                ],
            ],
        ]);

        $this->assertSame([
            'another' => 'anotherTemplate (Yaml)',
            'check' => 'checkTemplate',
            'default' => 'defaultTemplate (Yaml)',
        ], $choice->getChoices());

        $this->assertSame([
            'another' => 'anotherTemplate (Yaml)',
            'check' => 'checkTemplate',
            'default' => 'defaultTemplate (Yaml)',
        ], $choice->getChoices());

        $this->assertSame([
            'another' => 'anotherTemplate (Yaml)',
            'check' => 'checkTemplate',
            'default' => 'defaultTemplate (Yaml)',
        ], $choice->getChoices());

        $this->assertSame([
            'another' => 'anotherTemplate (Yaml)',
            'check' => 'checkTemplate',
            'default' => 'defaultTemplate (Yaml)',
        ], $choice->getChoices());
    }
}

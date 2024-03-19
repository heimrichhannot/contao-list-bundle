<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Test\DataContainer;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ListBundle\DataContainer\ListConfigContainer;
use HeimrichHannot\FilterBundle\Util\TwigSupportPolyfill\TwigTemplateLocator;
use PHPUnit\Framework\MockObject\MockObject;

class ListConfigTest extends ContaoTestCase
{
    public function createTestInstance(array $parameters = [])
    {
        if (!isset($parameters['bundleConfig'])) {
            $parameters['bundleConfig'] = [];
        }

        if (!isset($parameters['templateLocator'])) {
            /** @var TwigTemplateLocator|MockObject $templaceLocator */
            $templaceLocator = $this->createMock(TwigTemplateLocator::class);

            if (isset($parameters['templateGroup'])) {
                $templaceLocator->method('getTemplateGroup')->willReturn($parameters['templateGroup']);
            } else {
                $templaceLocator->method('getTemplateGroup')->willReturn([]);
            }
            $templaceLocator->method('getTemplatePath')->willReturnCallback(function ($templateName) {
                switch ($templateName) {
                   case 'default': return '@ListBundle/default.html.twig';
               }

                return '';
            });
            $parameters['templateLocator'] = $templaceLocator;
        }

        return new ListConfigContainer($parameters['bundleConfig'], $parameters['templateLocator']);
    }

    public function testTemplateOptionsCallback()
    {
        $this->assertEmpty($this->createTestInstance()->onListTemplateOptionsCallback());
        $this->assertEmpty($this->createTestInstance()->onItemTemplateOptionsCallback());
        $this->assertEmpty($this->createTestInstance()->onItemChoiceTemplateOptionsCallback());

        $instance = $this->createTestInstance(['templateGroup' => ['list_abc', 'rgreg', 'earg']]);
        $this->assertEmpty($instance->onListTemplateOptionsCallback());
        $this->assertEmpty($instance->onItemTemplateOptionsCallback());
        $this->assertEmpty($instance->onItemChoiceTemplateOptionsCallback());

        $this->assertEmpty($this->createTestInstance([
            'templateGroup' => ['list_abc', 'rgreg', 'earg'],
            'bundleConfig' => ['templates' => ['item_prefixes' => ['item_']]],
        ])->onListTemplateOptionsCallback());
        $this->assertCount(3, $this->createTestInstance([
            'templateGroup' => ['list_abc', 'rgreg', 'earg'],
            'bundleConfig' => ['templates' => ['list_prefixes' => ['list_']]],
        ])->onListTemplateOptionsCallback());

        $this->assertCount(3, $this->createTestInstance([
            'templateGroup' => ['list_abc' => '@App/list_abc.html.twig', 'rgreg' => '@Bundle/rgreg.html.twig', 'earg' => '@Bundle/earg.html.twig'],
            'bundleConfig' => [
                'templates' => [
                    'item_prefixes' => ['item_prefixes'],
                    'list' => [
                        ['name' => 'default', 'template' => '@ListBundle/default.html.twig'],
                    ],
                ],
            ],
        ])->onItemTemplateOptionsCallback());

        $this->assertArraySubset(['default' => '@ListBundle/default.html.twig (Yaml)'], $this->createTestInstance([
            'templateGroup' => ['list_abc' => '@App/list_abc.html.twig', 'default' => '@ListBundle/default.html.twig', 'earg' => '@App/earg.html.twig'],
            'bundleConfig' => [
                'templates' => [
                    'item_prefixes' => ['item_prefixes'],
                    'item' => [
                        ['name' => 'default', 'template' => '@ListBundle/default.html.twig'],
                    ],
                ],
            ],
        ])->onItemTemplateOptionsCallback());
        $this->assertCount(3, $this->createTestInstance([
            'templateGroup' => ['list_abc' => '@App/list_abc.html.twig', 'default' => '@ListBundle/default.html.twig', 'default2' => '@ListBundle/default.2html.twig'],
            'bundleConfig' => [
                'templates' => [
                    'item_prefixes' => ['item_prefixes'],
                    'item' => [
                        ['name' => 'default', 'template' => '@ListBundle/default.html.twig'],
                    ],
                ],
            ],
        ])->onItemTemplateOptionsCallback());
    }
}

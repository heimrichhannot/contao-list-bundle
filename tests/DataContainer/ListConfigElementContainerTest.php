<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Test\DataContainer;

use Contao\DC_Table;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ListBundle\ConfigElementType\ImageConfigElementType;
use HeimrichHannot\ListBundle\DataContainer\ListConfigElementContainer;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;
use HeimrichHannot\ListBundle\Registry\ListConfigElementRegistry;

class ListConfigElementContainerTest extends ContaoTestCase
{
    public function testGetConfigElementTypes()
    {
        $framework = $this->mockContaoFramework();
        $imageConfigElementType = new ImageConfigElementType($framework);

        $container = $this->mockContainer();
        $container->setParameter('huh.list', [
            'list' => [
                'config_element_types' => [
                    ['name' => 'image'],
                    ['name' => 'watchlist'],
                ],
            ],
        ]);

        $configElementRegistry = $this->createMock(ListConfigElementRegistry::class);
        $configElementRegistry->method('getConfigElementTypes')->willReturn(['image' => $imageConfigElementType]);
        $listConfigElementContainer = new ListConfigElementContainer($configElementRegistry, $container);
        $type = $listConfigElementContainer->getConfigElementTypes($this->createMock(DC_Table::class));

        $this->assertSame($type, ['image', 'watchlist']);
    }

    public function testOnLoadCallback()
    {
        $framework = $this->mockContaoFramework();
        $imageConfigElementType = new ImageConfigElementType($framework);

        $container = $this->mockContainer();

        $configElementRegistry = $this->createMock(ListConfigElementRegistry::class);
        $configElementRegistry->method('getConfigElementTypes')->willReturn(['image' => $imageConfigElementType]);
        $listConfigElementContainer = new ListConfigElementContainer($configElementRegistry, $container);

        $GLOBALS['TL_DCA'][ListConfigElementModel::getTable()]['palettes'] = [];

        $listConfigElementContainer->onLoadCallback([$this->createMock(DC_Table::class)]);
        $this->assertStringEndsWith(
            $imageConfigElementType->getPalette(),
            $GLOBALS['TL_DCA'][ListConfigElementModel::getTable()]['palettes'][$imageConfigElementType::getType()]
        );
        $this->assertStringStartsWith(
            '{title_type_legend},title,type,templateVariable;',
            $GLOBALS['TL_DCA'][ListConfigElementModel::getTable()]['palettes'][$imageConfigElementType::getType()]
        );
    }

    public function testListChildren()
    {
        $framework = $this->mockContaoFramework();
        $imageConfigElementType = new ImageConfigElementType($framework);

        $container = $this->mockContainer();
        $container->setParameter('huh.list', [
            'list' => [
                'config_element_types' => [
                    ['name' => 'image'],
                    ['name' => 'watchlist'],
                ],
            ],
        ]);

        $GLOBALS['TL_DCA']['tl_list_config_element']['fields']['type']['reference'] = [
            'image' => 'Image',
            'watchlist' => 'Watchlist',
        ];

        $configElementRegistry = $this->createMock(ListConfigElementRegistry::class);
        $configElementRegistry->method('getConfigElementTypes')->willReturn(['image' => $imageConfigElementType]);
        $listConfigElementContainer = new ListConfigElementContainer($configElementRegistry, $container);

        $result = $listConfigElementContainer->listChildren(['title' => 'Hallo', 'id' => 1, 'type' => 'image', 'dateAdded' => 0]);
        $this->assertNotFalse(stripos($result, 'Hallo'));
        $this->assertNotFalse(stripos($result, 'Image'));

        $result = $listConfigElementContainer->listChildren(['title' => '', 'id' => 1, 'type' => 'watchlist', 'dateAdded' => 951184922]);
        $this->assertNotFalse(stripos($result, '1'));
        $this->assertNotFalse(stripos($result, 'Watchlist'));
    }
}

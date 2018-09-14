<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Test\ConfigElementType;

use Contao\FilesModel;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ListBundle\ConfigElementType\ImageConfigElementType;
use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;

class ImageConfigElementTypeTest extends ContaoTestCase
{
    public function testAddToItemData()
    {
        $files = $this->mockAdapter(['findByUuid']);
        $files->method('findByUuid')->willReturnCallback(function ($uuid) {
            switch ($uuid) {
                default:
                case 'null':
                    return null;
            }
        });

        $framework = $this->mockContaoFramework([
            FilesModel::class => $files,
        ]);

        $container = $this->mockContainer();
        System::setContainer($container);

        $configElement = new ImageConfigElementType($framework);

        // No image
        $item = $this->createMock(ItemInterface::class);
        $item->expects($this->once())->method('getRawValue')->willReturn(null);
        $listConfigElement = $this->mockClassWithProperties(ListConfigElementModel::class, [
            'imageSelectorField' => null,
            'imageField' => '',
            'placeholderImageMode' => null,
        ]);
        $configElement->addToItemData($item, $listConfigElement);

        //has image
        $item = $this->createMock(ItemInterface::class);
        $item->expects($this->exactly(2))->method('getRawValue')->willReturnCallback(function ($field) {
            switch ($field) {
                case 'addImage':
                    return '1';
                case 'singleSRC':
                    return 'null';
            }
        });
        $listConfigElement = $this->mockClassWithProperties(ListConfigElementModel::class, [
            'imageSelectorField' => 'addImage',
            'imageField' => 'singleSrc',
            'placeholderImageMode' => null,
        ]);
        $configElement->addToItemData($item, $listConfigElement);
    }
}

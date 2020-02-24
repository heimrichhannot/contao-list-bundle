<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

// Keep namespace to overwrite system functions

namespace HeimrichHannot\ListBundle\ConfigElementType;

use Contao\FilesModel;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ListBundle\Backend\ListConfigElement;
use HeimrichHannot\ListBundle\Item\DefaultItem;
use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Image\ImageUtil;

function file_exists($path)
{
    return true;
}

function getimagesize($file)
{
    return [true];
}

class ImageConfigElementTypeTest extends ContaoTestCase
{
    public function testAddToItemData()
    {
        $files = $this->mockAdapter(['findByUuid']);
        $files->method('findByUuid')->willReturnCallback(function ($uuid) {
            switch ($uuid) {
                case 'image':
                    $file = $this->mockClassWithProperties(FilesModel::class, [
                        'path' => 'test',
                    ]);

                    return $file;

                case 'null':
                default:
                    return null;
            }
        });

        $containerUtil = $this->createMock(ContainerUtil::class);
        $containerUtil->method('getProjectDir');

        $imageUtil = $this->createMock(ImageUtil::class);
        $imageUtil->method('addToTemplateData');

        $framework = $this->mockContaoFramework([
            FilesModel::class => $files,
        ]);

        $container = $this->mockContainer();
        $container->set('huh.utils.container', $containerUtil);
        $container->set('huh.utils.image', $imageUtil);
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

        //has image, image not exist
        $item = $this->createMock(ItemInterface::class);
        $item->expects($this->exactly(3))->method('getRawValue')->willReturnCallback(function ($field) {
            switch ($field) {
                case 'addImage':
                    return '1';

                case 'singleSRC':
                    return 'null';
            }
        });
        $listConfigElement = $this->mockClassWithProperties(ListConfigElementModel::class, [
            'imageSelectorField' => 'addImage',
            'imageField' => 'singleSRC',
            'placeholderImageMode' => null,
        ]);
        $configElement->addToItemData($item, $listConfigElement);

        //no selector, has image, image not exist
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
            'imageSelectorField' => null,
            'imageField' => 'singleSRC',
            'placeholderImageMode' => null,
        ]);
        $configElement->addToItemData($item, $listConfigElement);

        //PlaceholderImage Simple, image not exist
        $item = $this->createMock(ItemInterface::class);
        $item->expects($this->once())->method('getRawValue')->willReturn(null);
        $listConfigElement = $this->mockClassWithProperties(ListConfigElementModel::class, [
            'imageSelectorField' => null,
            'imageField' => '',
            'placeholderImageMode' => ListConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE,
            'placeholderImage' => 'null',
        ]);
        $configElement->addToItemData($item, $listConfigElement);

        //PlaceholderImage gendered, image not exist
        $item = $this->createMock(ItemInterface::class);
        $item->expects($this->exactly(2))->method('getRawValue')->willReturn(null);
        $listConfigElement = $this->mockClassWithProperties(ListConfigElementModel::class, [
            'imageSelectorField' => null,
            'imageField' => '',
            'placeholderImageMode' => ListConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED,
            'placeholderImage' => 'null',
            'genderField' => 'genderField',
        ]);
        $configElement->addToItemData($item, $listConfigElement);

        //has image, images is array, image not exist
        $item = $this->createMock(ItemInterface::class);
        $item->expects($this->exactly(3))->method('getRawValue')->willReturnCallback(function ($field) {
            switch ($field) {
                case 'addImage':
                    return '1';

                case 'singleSRC':
                    return serialize(['image', 'test']);
            }
        });
        $values = [];
        $item->method('setFormattedValue')->willReturnCallback(function ($key, $value) use (&$values) {
            $values[$key] = $value;
        });
        $item->method('getFormattedValue')->willReturn(null);
        $item->method('getRaw')->willReturn([]);
        $listConfigElement = $this->mockClassWithProperties(ListConfigElementModel::class, [
            'imageSelectorField' => 'addImage',
            'imageField' => 'singleSRC',
            'placeholderImageMode' => null,
            'imgSize' => serialize([2, 2]),
        ]);
        $configElement->addToItemData($item, $listConfigElement);

        $this->assertSame([], $values['images']['singleSRC']);
    }

    public function testGetGenderedPlaceholderImage()
    {
        $framework = $this->mockContaoFramework();
        $configElement = new ImageConfigElementType($framework);

        $item = $this->createMock(ItemInterface::class);
        $item->method('getRawValue')->willReturnOnConsecutiveCalls('male', 'male', 'female', 'female', null);
        $listConfigElement = $this->mockClassWithProperties(ListConfigElementModel::class, [
            'placeholderImageMode' => ListConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED,
            'placeholderImage' => 'male',
            'placeholderImageFemale' => 'female',
            'genderField' => 'genderField',
        ]);

        $this->assertSame('male', $configElement->getGenderedPlaceholderImage($item, $listConfigElement));
        $this->assertSame('female', $configElement->getGenderedPlaceholderImage($item, $listConfigElement));
        $this->assertSame('male', $configElement->getGenderedPlaceholderImage($item, $listConfigElement));
    }

    public function testGetType()
    {
        $this->assertSame(ImageConfigElementType::getType(), ImageConfigElementType::TYPE);
    }

    public function testGetGetPalette()
    {
        $framework = $this->mockContaoFramework();
        $configElement = new ImageConfigElementType($framework);
        $this->assertStringStartsWith('{config_legend},imageSelectorField', $configElement->getPalette());
    }

    public function testAddToListItemData()
    {
        /** @var ItemInterface $item */
        $item = $this->mockClassWithProperties(DefaultItem::class, []);
        $listConfigElementModel = $this->mockClassWithProperties(ListConfigElementModel::class, []);
        $data = new ListConfigElementData($item, $listConfigElementModel);

        $imageConfigElementType = new ImageConfigElementType($this->mockContaoFramework());

        $before = $item->getRaw();

        $imageConfigElementType->addToListItemData($data);

        $this->assertSame($before, $item->getRaw());
    }
}

<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Test\ConfigElementType;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ListBundle\ConfigElementType\ListConfigElementData;
use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;

class ListConfigElementDataTest extends ContaoTestCase
{
    public function testContructor()
    {
        $item = $this->createMock(ItemInterface::class);
        $model = $this->mockClassWithProperties(ListConfigElementModel::class, []);

        $elementData = new ListConfigElementData($item, $model);
        $this->assertSame($elementData->getItem(), $item);
        $this->assertSame($elementData->getListConfigElement(), $model);

        $item = $this->createMock(ItemInterface::class);
        $model = $this->mockClassWithProperties(ListConfigElementModel::class, ['a' => 'b']);

        $elementData->setItem($item);
        $elementData->setListConfigElement($model);

        $this->assertSame($elementData->getItem(), $item);
        $this->assertSame($elementData->getListConfigElement(), $model);
    }
}

<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\ListBundle\Test\ConfigElementType;


use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ListBundle\ConfigElementType\ListConfigElementData;
use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;
use HeimrichHannot\UtilsBundle\Tests\ModelMockTrait;

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
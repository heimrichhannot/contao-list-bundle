<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Test\Choice;

use Contao\Model\Collection;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ListBundle\Choice\ParentListConfigChoice;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;

class ParentListConfigChoiceTest extends ContaoTestCase
{
    public function testCollect()
    {
        $listConfigRegistryMock = $this->createMock(ListConfigRegistry::class);
        $listConfigRegistryMock->method('findBy')->willReturnCallback(function ($column, $value, array $options = []) {
            if (\is_array($value)) {
                $value = $value[0];
            }

            switch ($value) {
                case 1:
                    $collection = $this->createMock(Collection::class);
                    $collection->method('fetchEach')->willReturnCallback(function ($key) {
                        switch ($key) {
                            case 'id':
                                return [1, 2, 3];

                            case 'title':
                                return ['A', 'B', 'C'];
                        }
                    });

                    return $collection;

                case 0:
                default:
                    return null;
            }
        });
        $container = $this->mockContainer();
        $container->set('huh.list.list-config-registry', $listConfigRegistryMock);
        System::setContainer($container);

        /** @var \PHPUnit_Framework_MockObject_MockObject|ParentListConfigChoice $choice */
        $choice = $this->getMockBuilder(ParentListConfigChoice::class)->disableOriginalConstructor()->setMethods(null)->getMock();

        $this->assertEmpty($choice->getChoices());
        $this->assertEmpty($choice->getChoices(['id' => null]));
        $this->assertEmpty($choice->getChoices(['id' => 0]));
        $this->assertSame([1 => 'A', 2 => 'B', 3 => 'C'], $choice->getChoices(['id' => 1]));
    }
}

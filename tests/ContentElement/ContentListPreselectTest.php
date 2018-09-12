<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Test\ContentElement;

use Contao\ContentModel;
use Contao\Controller;
use Contao\Model;
use Contao\Model\Collection;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\QueryBuilder;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\FilterBundle\Model\FilterPreselectModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\FilterBundle\Util\FilterPreselectUtil;
use HeimrichHannot\ListBundle\ContentElement\ContentListPreselect;
use HeimrichHannot\ListBundle\Event\ListModifyQueryBuilderEvent;
use HeimrichHannot\ListBundle\Exception\InterfaceNotImplementedException;
use HeimrichHannot\ListBundle\Lists\DefaultList;
use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Manager\ListManagerInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcherInterface;

class ContentListPreselectTest extends ContaoTestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ContentListPreselect
     */
    public function getContentElementMock()
    {
        $contentElement = $this->getMockBuilder(ContentListPreselect::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $contentElement->id = 1;

        return $contentElement;
    }

    public function testDoGenerate()
    {
        $controllerMock = $this->mockAdapter(['loadDataContainer', 'loadLanguageFile']);

        $framework = $this->mockContaoFramework([
            Controller::class => $controllerMock,
        ]);

        $preselectionCollectionMock = $this->createMock(Collection::class);
        $preselectionCollectionMock->method('getModels')->willReturn([]);
        $preselectModelMock = $this->createMock(FilterPreselectModel::class);
        $preselectModelMock->method('findPublishedByPidAndTableAndField')->willReturnOnConsecutiveCalls(null, $preselectionCollectionMock);

        $framework->method('createInstance')->willReturnCallback(function ($className) use (&$preselectModelMock) {
            switch ($className) {
                case FilterPreselectModel::class:
                    return $preselectModelMock;
            }
        });

        $filterQueryBuilder = $this->getFilterQueryBuilder();

        $filterManager = $this->createMock(FilterManager::class);
        $filterManager->method('findById')->willReturnCallback(function ($id) {
            switch ($id) {
                case 4:
                    $filterConfig = $this->createMock(FilterConfig::class);
                    $filterConfig->method('getId')->willReturn('4');
                    $filterConfig->method('getElements')->willReturn($this->createMock(Collection::class));
                    $filterConfig->expects($this->never())->method('resetData');
                    $filterConfig->expects($this->exactly(1))->method('setData');

                    return $filterConfig;
                case 3:
                    $filterConfig = $this->createMock(FilterConfig::class);
                    $filterConfig->method('getId')->willReturn('3');
                    $filterConfig->method('getElements')->willReturn($this->createMock(Collection::class));
                    $filterConfig->expects($this->exactly(1))->method('resetData');
                    $filterConfig->expects($this->never())->method('setData');

                    return $filterConfig;
                case 2:
                    $filterConfig = $this->createMock(FilterConfig::class);
                    $filterConfig->method('getId')->willReturn('2');
                    $filterConfig->method('getElements')->willReturn(null);
                    $filterConfig->expects($this->never())->method('resetData');
                    $filterConfig->expects($this->never())->method('setData');

                    return $filterConfig;
                case 1:
                    $filterConfig = $this->createMock(FilterConfig::class);
                    $filterConfig->method('getId')->willReturn('1');

                    return $filterConfig;
                case 0:
                default:
                    return null;
            }
        });
        $filterManager->method('getQueryBuilder')->willReturnOnConsecutiveCalls(null, $filterQueryBuilder, $filterQueryBuilder, $filterQueryBuilder);

        $listManager = $this->createMock(ListManagerInterface::class);
        $listManager->method('getListByName')->willReturnCallback(function ($listName) {
            switch ($listName) {
                case 'noInterface':
                    return \stdClass::class;
                case 'list':
                    return ListInterface::class;
                case 'default':
                    return DefaultList::class;
                case 'null':
                default:
                    return null;
            }
        });
        $listManager->expects($this->exactly(1))->method('setList');

        $emptyList = $this->createMock(ListInterface::class);
        $emptyList->method('getItems')->willReturn(null);
        $filledList = $this->createMock(ListInterface::class);
        $filledList->method('getItems')->willReturn(['a', 'b', 'c']);

        $listManager->method('getList')->willReturnOnConsecutiveCalls(
            $filledList, $emptyList, $emptyList, $emptyList, $emptyList, $filledList, $filledList, $filledList, $filledList
        );
        $listManager->method('getListConfig')->willReturnOnConsecutiveCalls(
            $this->mockClassWithProperties(ListConfigModel::class, ['doNotRenderEmpty' => '1']),
            $this->mockClassWithProperties(ListConfigModel::class, ['doNotRenderEmpty' => '1']),
            $this->mockClassWithProperties(ListConfigModel::class, ['doNotRenderEmpty' => '1']),
            $this->mockClassWithProperties(ListConfigModel::class, ['doNotRenderEmpty' => '1']),
            $this->mockClassWithProperties(ListConfigModel::class, ['doNotRenderEmpty' => '0']),
            $this->mockClassWithProperties(ListConfigModel::class, ['doNotRenderEmpty' => '0']),
            $this->mockClassWithProperties(ListConfigModel::class, ['doNotRenderEmpty' => '0']),
            $this->mockClassWithProperties(ListConfigModel::class, ['doNotRenderEmpty' => '0']),
            $this->mockClassWithProperties(ListConfigModel::class, ['doNotRenderEmpty' => '0'])
        );
        $listManager->expects($this->exactly(3))->method('getFilterManager')->willReturn($filterManager);

        $eventDispatcher = $this->createMock(TraceableEventDispatcherInterface::class);

        $filterPreselectUtilMock = $this->createMock(FilterPreselectUtil::class);
        $filterPreselectUtilMock->method('getPreselectData');

        $container = $this->mockContainer();
        $container->set('contao.framework', $framework);
        $container->set('huh.filter.manager', $filterManager);
        $container->set('event_dispatcher', $eventDispatcher);
        $container->set('huh.filter.util.filter_preselect', $filterPreselectUtilMock);
        System::setContainer($container);

        $reflectionClass = new \ReflectionClass(ContentListPreselect::class);
        $modelProperty = $reflectionClass->getProperty('objModel');
        $modelProperty->setAccessible(true);
        $filterConfigProperty = $reflectionClass->getProperty('filterConfig');
        $filterConfigProperty->setAccessible(true);
        $filterProperty = $reflectionClass->getProperty('filter');
        $filterProperty->setAccessible(true);
        $managerProperty = $reflectionClass->getProperty('manager');
        $managerProperty->setAccessible(true);
        $listConfigProperty = $reflectionClass->getProperty('listConfig');
        $listConfigProperty->setAccessible(true);

        // No Manager set
        $contentElement = $this->getContentElementMock();
        $this->assertFalse($contentElement->doGenerate());

        // Empty values
        $managerProperty->setValue($contentElement, $listManager);
        $model = $this->mockClassWithProperties(ContentModel::class, []);
        $modelProperty->setValue($contentElement, $model);
        $this->assertFalse($contentElement->doGenerate());

        // One complete run without going into if's
        $model = $this->mockClassWithProperties(ContentModel::class, ['listPreselect' => 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}']);
        $modelProperty->setValue($contentElement, $model);
        $filterConfig = $this->createMock(FilterConfig::class);
        $filterConfig->method('getId')->willReturn('0');
        $filterConfigProperty->setValue($contentElement, $filterConfig);
        $filter = new \stdClass();
        $filter->dataContainer = 'tl_content';
        $filter->id = 1;
        $filterProperty->setValue($contentElement, $filter);
        $listConfig = new \stdClass();
        $listConfig->list = 'null';
        $listConfigProperty->setValue($contentElement, $listConfig);
        $this->assertTrue($contentElement->doGenerate());

        // doNotRenderEmpty true, QueryBuilder false
        $this->assertFalse($contentElement->doGenerate());

        // doNotRenderEmpty true, QueryBuilder true, total count 0
        $this->assertFalse($contentElement->doGenerate());

        // doNotRenderEmpty true, QueryBuilder true, total count true
        $this->assertTrue($contentElement->doGenerate());

        // Test preselect, no elements, doNotRenderEmpty false
        $filterConfig = $this->createMock(FilterConfig::class);
        $filterConfig->method('getId')->willReturn('2');
        $filterConfigProperty->setValue($contentElement, $filterConfig);
        $this->assertTrue($contentElement->doGenerate());

        // Test preselect, elements, doNotRenderEmpty false
        $filterConfig = $this->createMock(FilterConfig::class);
        $filterConfig->method('getId')->willReturn('3');
        $filterConfigProperty->setValue($contentElement, $filterConfig);
        $this->assertTrue($contentElement->doGenerate());

        // Test preselect, elements, doNotRenderEmpty false
        $filterConfig = $this->createMock(FilterConfig::class);
        $filterConfig->method('getId')->willReturn('4');
        $filterConfigProperty->setValue($contentElement, $filterConfig);
        $this->assertTrue($contentElement->doGenerate());

        $filterConfig = $this->createMock(FilterConfig::class);
        $filterConfig->method('getId')->willReturn('0');
        $filterConfigProperty->setValue($contentElement, $filterConfig);

        $listConfig = new \stdClass();
        $listConfig->list = 'default';
        $listConfigProperty->setValue($contentElement, $listConfig);
        $contentElement->doGenerate();

        $listConfig = new \stdClass();
        $listConfig->list = 'noInterface';
        $listConfigProperty->setValue($contentElement, $listConfig);
        $error = false;
        try {
            $contentElement->doGenerate();
        } catch (InterfaceNotImplementedException $e) {
            $error = true;
        }
        $this->assertTrue($error);

        $listConfig = new \stdClass();
        $listConfig->list = 'list';
        $listConfigProperty->setValue($contentElement, $listConfig);
        $error = false;
        try {
            $contentElement->doGenerate();
        } catch (InterfaceNotImplementedException $e) {
            $error = true;
        }
        $this->assertTrue($error);
    }

    public function skiptestDoGenerateWithException()
    {
        $listConfig = new \stdClass();
        $listConfig->list = 'default';
        $listConfigProperty->setValue($contentElement, $listConfig);
        $this->expectException(InterfaceNotImplementedException::class);
        $contentElement->doGenerate();
    }

    public function testListModifyQueryBuilder()
    {
        $controllerMock = $this->mockAdapter(['replaceInsertTags']);
        $controllerMock->method('replaceInsertTags')->willReturnArgument(0);
        $modelMock = $this->mockAdapter(['getClassFromTable']);
        $modelMock->method('getClassFromTable')->willReturnOnConsecutiveCalls('', ContentModel::class);

        $framework = $this->mockContaoFramework([
            Model::class => $modelMock,
            Controller::class => $controllerMock,
        ]);

        $dataBaseUtilMock = $this->createMock(DatabaseUtil::class);
        $dataBaseUtilMock->method('composeWhereForQueryBuilder')->willReturnCallback(function (QueryBuilder $queryBuilder, string $field, string $operator, array $dca = null, $value = null) {
            return [
                'queryBuilder' => $queryBuilder,
                'field' => $field,
                'operator' => $operator,
                'dca' => $dca,
                'value' => $value,
            ];
        });

        $container = $this->mockContainer();
        $container->set('contao.framework', $framework);
        $container->set('huh.utils.database', $dataBaseUtilMock);
        System::setContainer($container);

        $reflectionClass = new \ReflectionClass(ContentListPreselect::class);
        $modelProperty = $reflectionClass->getProperty('objModel');
        $modelProperty->setAccessible(true);
        $filterProperty = $reflectionClass->getProperty('filter');
        $filterProperty->setAccessible(true);

        $filter = ['dataContainer' => 'tl_content'];

        $GLOBALS['TL_DCA'][$filter['dataContainer']] = [];

        $contentElement = $this->getContentElementMock();
        $filterProperty->setValue($contentElement, (object) $filter);

        $filterConfig = $this->createMock(FilterConfig::class);
        $filterConfig->method('getFilter')->willReturn($filter);

        $listManager = $this->createMock(ListManagerInterface::class);
        $listManager->method('getFilterConfig')->willReturn($filterConfig);

        $list = $this->createMock(ListInterface::class);
        $list->method('getManager')->willReturn($listManager);
        $listConfigModel = $this->mockClassWithProperties(ListConfigModel::class, []);

        $andWhere = null;
        $add = null;

        $filterQueryBuilder = $this->getFilterQueryBuilder();
        $filterQueryBuilder->method('andWhere')->willReturnCallback(function ($where) use (&$andWhere) {
            $andWhere = $where;
        });
        $filterQueryBuilder->method('add')->willReturnCallback(function ($sqlPartName, $sqlPart, $append = false) use (&$add) {
            $add = [$sqlPartName, $sqlPart, $append];
        });

        // No Table class
        $event = new ListModifyQueryBuilderEvent($filterQueryBuilder, $list, $listConfigModel);
        $andWhere = null;
        $add = null;
        $contentModelMock = $this->mockClassWithProperties(ContentModel::class, [
            'listPreselect' => 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}',
        ]);
        $modelProperty->setValue($contentElement, $contentModelMock);
        $contentElement->listModifyQueryBuilder($event);
        $this->assertSame('tl_content.id', $andWhere['field']);

        // Table class, no preselect
        $event = new ListModifyQueryBuilderEvent($filterQueryBuilder, $list, $listConfigModel);
        $andWhere = null;
        $add = null;
        $contentModelMock = $this->mockClassWithProperties(ContentModel::class, [
            'listPreselect' => '',
        ]);
        $modelProperty->setValue($contentElement, $contentModelMock);
        $contentElement->listModifyQueryBuilder($event);
        $this->assertSame('tl_content.id', $andWhere['field']);
    }

    /**
     * @param $statement
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|FilterQueryBuilder
     */
    protected function getFilterQueryBuilder()
    {
        $statement = $this->createMock(Statement::class);
        $statement->method('rowCount')->willReturnOnConsecutiveCalls(0, 2);

        $filterQueryBuilder = $this->createMock(FilterQueryBuilder::class);
        $filterQueryBuilder->method('select')->willReturnSelf();
        $filterQueryBuilder->method('execute')->willReturn($statement);

        return $filterQueryBuilder;
    }
}

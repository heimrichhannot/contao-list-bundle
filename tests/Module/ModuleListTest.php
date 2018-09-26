<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Test\Module;

use Contao\Controller;
use Contao\FrontendTemplate;
use Contao\ModuleModel;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Driver\Statement;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\ListBundle\Exception\InterfaceNotImplementedException;
use HeimrichHannot\ListBundle\Lists\DefaultList;
use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Manager\ListManager;
use HeimrichHannot\ListBundle\Manager\ListManagerInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\ListBundle\Module\ModuleList;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ModuleListTest extends ContaoTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $container = $this->mockContainer();
        $container->set('event_dispatcher', $this->createMock(EventDispatcher::class));
        System::setContainer($container);
    }

    public function getContaoFramework()
    {
        $controllerMock = $this->mockAdapter(['loadDataContainer', 'loadLanguageFile']);

        $framework = $this->mockContaoFramework([
            Controller::class => $controllerMock,
        ]);

        return $framework;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ListManagerInterface
     */
    public function getListManagerMock(string $doNotRenderEmpty = '0', int $itemCount = 0, int $queryResults = 0)
    {
        $listManager = $this->createMock(ListManager::class);
        $listManager->method('getListByName')->willReturnCallback(function ($listName) {
            switch ($listName) {
                case 'default':
                    return DefaultList::class;
                case 'listOnly':
                    return ListInterface::class;
                case 'none':
                    return \stdClass::class;
            }
        });
        $listConfig = $this->mockClassWithProperties(ListConfigModel::class, [
            'doNotRenderEmpty' => $doNotRenderEmpty,
            'noSearch' => '0',
        ]);
        $listManager->method('getListConfig')->willReturn($listConfig);
        $listManager->method('getList')->willReturn($this->getListMock($itemCount));
        $listManager->method('getFilterManager')->willReturn($this->getFilterManager($queryResults));

        return $listManager;
    }

    /**
     * @param int $itemCount
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|ListInterface
     */
    public function getListMock(int $itemCount = 0)
    {
        $list = $this->getMockBuilder([ListInterface::class, \JsonSerializable::class])->getMock();

        $items = null;
        if ($itemCount > 0) {
            $items = array_fill(0, $itemCount, null);
        }
        $list->method('getItems')->willReturn($items);
        $list->method('handleShare');
        $list->method('parse')->willReturn('');

        return $list;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ListConfigModel
     */
    public function getListConfigModelMock(array $listConfigData = [])
    {
        $listConfigModel = $this->mockClassWithProperties(ListConfigModel::class, $listConfigData);

        return $listConfigModel;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FilterManager
     */
    public function getFilterManager(int $queryResults = 0)
    {
        $filterManager = $this->createMock(FilterManager::class);
        $filterManager->method('getQueryBuilder')->willReturnCallback(function ($id) use ($queryResults) {
            $statement = $this->createMock(Statement::class);
            $statement->method('rowCount')->willReturn($queryResults);

            $filterQueryBuilder = $this->createMock(FilterQueryBuilder::class);
            $filterQueryBuilder->method('select')->willReturnSelf();
            $filterQueryBuilder->method('execute')->willReturn($statement);

            return $filterQueryBuilder;
        });

        return $filterManager;
    }

    public function getModuleMock(
        ?array $listConfigData = [],
        ?array $filter = ['dataContainer' => 'tl_content'],
        ?string $doNotRenderEmpty = '0',
        ?int $itemCount = 0,
        ?int $queryResults = 0
    ) {
        $listConfigData = $listConfigData ?? [];
        $filter = $filter ?? ['dataContainer' => 'tl_content'];
        $doNotRenderEmpty = $doNotRenderEmpty ?? '0';
        $itemCount = $itemCount ?? 0;
        $queryResults = $queryResults ?? 0;

        /** @var \PHPUnit_Framework_MockObject_MockObject|ModuleList $module */
        $module = $this->getMockBuilder(ModuleList::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $model = $this->mockClassWithProperties(ModuleModel::class, []);
        $listConfigRegistry = $this->createMock(ListConfigRegistry::class);

        $filterConfig = $this->createMock(FilterConfig::class);
        $filterConfig->method('getFilter')->willReturn($filter);

        $module->initModule(
            $model,
            $this->getContaoFramework(),
            $listConfigRegistry,
            $this->getFilterManager(),
            $this->getListManagerMock($doNotRenderEmpty, $itemCount, $queryResults),
            $this->getListConfigModelMock($listConfigData),
            $filterConfig
        );

        return $module;
    }

    public function testDoGenerate()
    {
        $listConfigData = ['list' => 'none'];
        $module = $this->getModuleMock($listConfigData);
        $error = false;
        $errorInterface = '';
        try {
            $module->doGenerate();
        } catch (InterfaceNotImplementedException $e) {
            $error = true;
            $errorInterface = $e->getInterface();
        }
        $this->assertTrue($error);
        $this->assertSame(ListInterface::class, $errorInterface);

        $listConfigData = ['list' => 'listOnly'];
        $module = $this->getModuleMock($listConfigData);
        $error = false;
        $errorInterface = '';
        try {
            $module->doGenerate();
        } catch (InterfaceNotImplementedException $e) {
            $error = true;
            $errorInterface = $e->getInterface();
        }
        $this->assertTrue($error);
        $this->assertSame(\JsonSerializable::class, $errorInterface);

        $module = $this->getModuleMock(null, null, '0');
        $this->assertTrue($module->doGenerate());

        $module = $this->getModuleMock(null, null, '1', 2);
        $this->assertTrue($module->doGenerate());

        $module = $this->getModuleMock(null, ['dataContainer' => 'tl_content', 'id' => '4'], '1', 0, 2);
        $this->assertTrue($module->doGenerate());

        $module = $this->getModuleMock(null, ['dataContainer' => 'tl_content', 'id' => '4'], '1', 0, 0);
        $this->assertFalse($module->doGenerate());
    }

    public function testGetFilterConfig()
    {
        $module = $this->getModuleMock();
        $this->assertInstanceOf(FilterConfig::class, $module->getFilterConfig());
    }

    public function testGetManager()
    {
        $module = $this->getModuleMock();
        $this->assertInstanceOf(ListManagerInterface::class, $module->getManager());
    }

    public function testGetListManager()
    {
        $module = $this->getModuleMock();
        $this->assertInstanceOf(ListManagerInterface::class, $module->getListManager());
    }

    public function testDoCompile()
    {
        $module = $this->getModuleMock();

        $properties = [
            'noSearch' => true,
        ];

        $template = $this->mockClassWithProperties(FrontendTemplate::class, []);

        $template
            ->method('__set')
            ->willReturnCallback(
                function (string $key, $value) use (&$properties) {
                    $properties[$key] = $value;
                }
            );
        $cssID = ['id', 'class'];

        $cssID = $module->doCompile($template, $cssID);

        $this->assertSame('id', $cssID[0]);
        $this->assertSame('class huh-list', $cssID[1]);

        $this->assertFalse($properties['noSearch']);

        \call_user_func($properties['list'], 'default', 'default', []);
    }
}

<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Test\DataContainer;

use Contao\ContentModel;
use Contao\DataContainer;
use Contao\Model;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Driver\Statement;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Model\FilterPreselectModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\FilterBundle\Util\FilterPreselectUtil;
use HeimrichHannot\ListBundle\DataContainer\ContentContainer;
use HeimrichHannot\ListBundle\Exception\InvalidListConfigException;
use HeimrichHannot\ListBundle\Exception\InvalidListManagerException;
use HeimrichHannot\ListBundle\Manager\ListManager;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;
use HeimrichHannot\ListBundle\Util\ListManagerUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class ContentContainerTest extends ContaoTestCase
{
    public function getContentContainerInstance($framework = null)
    {
        $modelMock = $this->mockAdapter(['getClassFromTable']);
        $modelMock->method('getClassFromTable')->willReturnCallback(function ($table) {
            switch ($table) {
                case 'tl_content':
                    return ContentModel::class;
//					$model = $modelMock = $this->mockClassWithProperties(ContentModel::class, []);
//					$model->method('getPk')->willReturn('id');
//					return $model;
            }
        });

        if (!$framework) {
            $framework = $this->mockContaoFramework([
                Model::class => $modelMock,
            ]);
            $framework->method('createInstance')->willReturnCallback(function ($class) {
                switch ($class) {
                    case FilterPreselectModel::class:
                        $filterPreselectModelMock = $this->mockClassWithProperties(FilterPreselectModel::class, []);
                        $filterPreselectModelMock->method('findPublishedByPidAndTableAndField')->willReturnCallback(function ($id, $table, $parentField) {
                            if (6 === $id) {
                                $preselectionsMock = $this->mockClassWithProperties(Model\Collection::class, []);
                                $preselectionsMock->method('getModels')->willReturn([]);

                                return $preselectionsMock;
                            }

                            return null;
                        });

                        return $filterPreselectModelMock;
                }
            });
        }
        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findModelInstanceByPk')->willReturnCallback(function ($table, $id) {
            switch ($id) {
                case 9:
                    $model = $this->mockClassWithProperties(ContentModel::class, ['id' => 9, 'listConfig' => 6, 'filterConfig' => 1]);

                    break;

                case 8:
                    $model = $this->mockClassWithProperties(ContentModel::class, ['id' => 8, 'listConfig' => 5, 'filterConfig' => 2]);
                    $model->expects($this->exactly(1))->method('save')->willReturn(true);

                    break;

                case 7:
                    $model = $this->mockClassWithProperties(ContentModel::class, ['id' => 7, 'listConfig' => 4]);

                    break;

                case 6:
                    $model = $this->mockClassWithProperties(ContentModel::class, ['id' => 6, 'listConfig' => 3]);

                    break;

                case 5:
                    $model = $this->mockClassWithProperties(ContentModel::class, ['id' => 5, 'listConfig' => 2]);

                    break;

                case 4:
                    $model = $this->mockClassWithProperties(ContentModel::class, ['id' => 4, 'listConfig' => 1]);

                    break;

                case 3:
                    $model = $this->mockClassWithProperties(ContentModel::class, ['id' => 3, 'listConfig' => 0]);

                    break;

                case 2:
                    $model = $this->mockClassWithProperties(ContentModel::class, ['id' => 2, 'listConfig' => null]);

                    break;

                case 1:
                    $model = $this->mockClassWithProperties(ContentModel::class, ['id' => 1, 'listConfig' => '']);

                    break;

                case 0:
                default:
                    $model = null;

                    break;
            }

            return $model;
        });
        $listConfigRegistryMock = $this->createMock(ListConfigRegistry::class);
        $listConfigRegistryMock->method('findByPk')->willReturnCallback(function ($pk) {
            switch ($pk) {
                case 6:
                    return $this->mockClassWithProperties(ListConfigModel::class, ['filter' => 1]);

                case 5:
                    return $this->mockClassWithProperties(ListConfigModel::class, ['filter' => 1]);

                case 4:
                    return $this->mockClassWithProperties(ListConfigModel::class, ['filter' => 0]);

                case 3:
                    return $this->mockClassWithProperties(ListConfigModel::class, ['filter' => null]);

                case 2:
                    return $this->mockClassWithProperties(ListConfigModel::class, []);

                case 1:
                case 0:
                default:
                    return null;
            }
        });
        $listConfigRegistryMock->method('getComputedListConfig')->willReturnCallback(function ($listConfig) {
            switch ($listConfig) {
                case 4:
                    return $this->mockClassWithProperties(ListConfigModel::class, ['manager' => 'default']);

                case 3:
                    return $this->mockClassWithProperties(ListConfigModel::class, ['manager' => 'default']);

                case 2:
                    return $this->mockClassWithProperties(ListConfigModel::class, ['manager' => 'error']);

                case 1:
                default:
                    throw new InvalidListConfigException();
            }
        });

        $statementMock = $this->createMock(Statement::class);
        $statementMock->method('fetchAll')->willReturnOnConsecutiveCalls([], [['id' => 2], ['id' => 3]]);

        $queryBuilderMock = $this->getMockBuilder(FilterQueryBuilder::class)->disableOriginalConstructor()->getMock();
        $queryBuilderMock->method('resetQueryParts')->willReturnSelf();
        $queryBuilderMock->method('select')->willReturnSelf();
        $queryBuilderMock->method('from')->willReturnSelf();
        $queryBuilderMock->method('execute')->willReturn($statementMock);

        $filterConfigMock = $this->createMock(FilterConfig::class);
        $filterConfigMock->method('initQueryBuilder');
        $filterConfigMock->method('getQueryBuilder')->willReturn($queryBuilderMock);
        $filterConfigMock->method('getFilter')->willReturn(['dataContainer' => 'tl_content']);
        $filterConfigMock->method('getId')->willReturn(1);

        $listManagerUtil = $this->createMock(ListManagerUtil::class);
        $listManagerUtil->method('getListManagerByName')->willReturnCallback(function ($name) use ($filterConfigMock) {
            switch ($name) {
                case 'default':
                    $manager = $this->createMock(ListManager::class);
                    $manager->method('setListConfig');
                    $manager->method('getFilterConfig')->willReturn($filterConfigMock);

                    return $manager;

                case 'error':
                default:
                    throw new InvalidListManagerException();
            }
        });
        $filterPreseletUtil = $this->getMockBuilder(FilterPreselectUtil::class)->disableOriginalConstructor()->setMethods(['getPreselectQueryBuilder'])->getMock();
        $filterPreseletUtil->method('getPreselectQueryBuilder')->willReturn($queryBuilderMock);
        $twig = $this->createMock(\Twig_Environment::class);
        $twig->method('render')->willReturn(true);

        return new ContentContainer($framework, $modelUtil, $listConfigRegistryMock, $listManagerUtil, $filterPreseletUtil, $twig);
    }

    public function testOnLoad()
    {
        $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect'] = 'listConfig;';
        $GLOBALS['TL_DCA']['tl_content']['fields']['filterPreselect']['eval']['submitOnChange'] = false;
        $contentContainer = $this->getContentContainerInstance();
        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_content', 'id' => 0]);
        $contentContainer->onLoad($dc);
        $this->assertSame('listConfig;', $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect']);

        $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect'] = 'listConfig;';
        $contentContainer = $this->getContentContainerInstance();
        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_content', 'id' => 1]);
        $contentContainer->onLoad($dc);
        $this->assertSame('listConfig;', $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect']);

        $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect'] = 'listConfig;';
        $contentContainer = $this->getContentContainerInstance();
        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_content', 'id' => 2]);
        $contentContainer->onLoad($dc);
        $this->assertSame('listConfig;', $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect']);

        $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect'] = 'listConfig;';
        $contentContainer = $this->getContentContainerInstance();
        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_content', 'id' => 3]);
        $contentContainer->onLoad($dc);
        $this->assertSame('listConfig;', $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect']);

        $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect'] = 'listConfig;';
        $contentContainer = $this->getContentContainerInstance();
        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_content', 'id' => 4]);
        $contentContainer->onLoad($dc);
        $this->assertSame('listConfig;', $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect']);

        $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect'] = 'listConfig;';
        $contentContainer = $this->getContentContainerInstance();
        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_content', 'id' => 5]);
        $contentContainer->onLoad($dc);
        $this->assertSame('listConfig;', $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect']);

        $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect'] = 'listConfig;';
        $contentContainer = $this->getContentContainerInstance();
        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_content', 'id' => 6]);
        $contentContainer->onLoad($dc);
        $this->assertSame('listConfig;', $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect']);

        $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect'] = 'listConfig;';
        $contentContainer = $this->getContentContainerInstance();
        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_content', 'id' => 7]);
        $contentContainer->onLoad($dc);
        $this->assertSame('listConfig;', $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect']);
        $this->assertFalse($GLOBALS['TL_DCA']['tl_content']['fields']['filterPreselect']['eval']['submitOnChange']);

        $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect'] = 'listConfig;';
        $contentContainer = $this->getContentContainerInstance();
        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_content', 'id' => 8]);
        $contentContainer->onLoad($dc);
        $this->assertSame('listConfig,filterPreselect,listPreselect;', $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect']);

        $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect'] = 'listConfig;';
        $contentContainer = $this->getContentContainerInstance();
        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_content', 'id' => 9]);
        $contentContainer->onLoad($dc);
        $this->assertSame('listConfig,filterPreselect,listPreselect;', $GLOBALS['TL_DCA']['tl_content']['palettes']['list_preselect']);
    }

    public function testGetListPreselectChoices()
    {
        $contentContainer = $this->getContentContainerInstance();

        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_content', 'id' => 0]);
        $this->assertSame([], $contentContainer->getListPreselectChoices($dc));
        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_content', 'id' => 1]);
        $this->assertSame([], $contentContainer->getListPreselectChoices($dc));
        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_content', 'id' => 2]);
        $this->assertSame([], $contentContainer->getListPreselectChoices($dc));
        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_content', 'id' => 3]);
        $this->assertSame([], $contentContainer->getListPreselectChoices($dc));
        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_content', 'id' => 4]);
        $this->assertSame([], $contentContainer->getListPreselectChoices($dc));
        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_content', 'id' => 5]);
        $this->assertSame([], $contentContainer->getListPreselectChoices($dc));
        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_content', 'id' => 6]);
        $this->assertSame([], $contentContainer->getListPreselectChoices($dc));
        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_content', 'id' => 7]);
        $this->assertSame([2 => true, 3 => true], $contentContainer->getListPreselectChoices($dc));
    }
}

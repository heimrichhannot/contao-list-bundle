<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Tests\Module;

use Contao\Config;
use Contao\Model;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\System;
use Contao\TemplateLoader;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Registry\FilterRegistry;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\ListBundle\Module\ModuleList;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class ModuleListTest extends ContaoTestCase
{
    public function setUp()
    {
        $objPage = $this->getMockBuilder(PageModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objPage->outputFormat = '';

        $GLOBALS['TL_LANGUAGE'] = 'de';
        $GLOBALS['objPage'] = $objPage;
        $GLOBALS['TL_DCA']['tl_list_config']['fields']['abc']['eval']['addAsDataAttribute'] = true;
        $GLOBALS['TL_DCA']['tl_list_config']['fields']['fieldWithData']['eval']['addAsDataAttribute'] = true;
        $GLOBALS['TL_DCA']['tl_list_config']['fields']['fieldWithoutData']['eval']['addAsDataAttribute'] = false;

        $router = $this->createRouterMock();
        $requestStack = $this->createRequestStackMock();
        $framework = $this->mockContaoFramework($this->createMockAdapater());

        $config = $this->createMock(Config::class);
        $database = $this->createMock(Connection::class);

        $container = $this->mockContainer();
        $container->set('request_stack', $requestStack);
        $container->set('router', $router);
        $container->set('contao.framework', $framework);
        $container->set('database_connection', $database);
        $container->set('huh.list.list-config-registry', $this->createListConfigRegistry());
        $container->set('huh.filter.registry', $this->createFilterRegistry());
        $container->set('huh.utils.string', new StringUtil($framework));
        System::setContainer($container);
    }

    public function testCanBeInstantiated()
    {
        $moduleModel = $this->mockClassWithProperties(ModuleModel::class, ['id' => 1]);
        $module = new ModuleList($moduleModel);
        $this->assertInstanceOf(ModuleList::class, $module);
    }

    /**
     * Noch nicht fertiggestellt.
     */
    public function skip_testGenerate()
    {
        TemplateLoader::addFiles(['mod_list' => '../src/Resources/contao/templates']);

        $moduleModelConfig = [
            'id' => 1,
            'listConfig' => 5,
            'cssID' => [0 => 'phpunit', 1 => 'test'],
        ];
        $moduleModel = $this->getMockBuilder(ModuleModel::class)->disableOriginalConstructor()
            ->setMethods(['row'])
            ->getMock();
        $moduleModel->method('row')->willReturn($moduleModelConfig);
        foreach ($moduleModelConfig as $key => $value) {
            $moduleModel->$key = $value;
        }
        $module = new ModuleList($moduleModel);
        $module->generate();
    }

    public function createRouterMock()
    {
        $router = $this->createMock(RouterInterface::class);
        $router
            ->method('generate')
            ->with('contao_backend', $this->anything())
            ->will($this->returnCallback(function ($route, $params = []) {
                $url = '/contao';
                if (!empty($params)) {
                    $count = 0;
                    foreach ($params as $key => $value) {
                        $url .= (0 === $count ? '?' : '&');
                        $url .= $key.'='.$value;
                        ++$count;
                    }
                }

                return $url;
            }));

        return $router;
    }

    public function createRequestStackMock()
    {
        $requestStack = new RequestStack();
        $request = new Request();
        $request->attributes->set('_contao_referer_id', 'foobar');
        $requestStack->push($request);

        return $requestStack;
    }

    public function createMockAdapater()
    {
        $systemAdapter = $this->mockAdapter(['loadLanguageFile']);
//        $modelAdapter = $this->mockAdapter(['__construct']);
//        $modelAdapter->method('__construct')->will

        return [
//            Model::class => $modelAdapter
            System::class => $systemAdapter,
        ];
    }

    public function createListConfigRegistry()
    {
        $listModelData = [
            'id' => 5,
            'filter' => 3,
        ];

        $listRegistryModel = $this->getMockBuilder(ListConfigModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['row', '__set'])
            ->getMock();
        $listRegistryModel->method('row')->willReturn($listModelData);

        $listRegistryModel->filter = 3;

        $listRegistry = $this->createConfiguredMock(ListConfigRegistry::class, [
            'findByPk' => $listRegistryModel,
        ]);

        return $listRegistry;
    }

    public function createFilterRegistry()
    {
        $filterConfig = $this->getMockBuilder(FilterConfig::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFilter', 'hasData'])
            ->getMock()
        ;
        $filterConfig->method('getFilter')->willReturn([
            'dataContainer' => 'tl_news',
        ]);
        $filterConfig->method('hasData')->willReturn(true);

        $filterRegistryConfig = [
            'findById' => $filterConfig,
        ];
        $filterRegistry = $this->createConfiguredMock(FilterRegistry::class, $filterRegistryConfig);

        return $filterRegistry;
    }
}

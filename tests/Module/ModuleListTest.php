<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Test\Module;

use Contao\Config;
use Contao\CoreBundle\Config\ResourceFinder;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Model;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\System;
use Contao\TemplateLoader;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\MySqlSchemaManager;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use HeimrichHannot\ListBundle\Manager\ListManager;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\ListBundle\Module\ModuleList;
use HeimrichHannot\ListBundle\Registry\ListConfigElementRegistry;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Form\FormUtil;
use HeimrichHannot\UtilsBundle\Image\ImageUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouterInterface;

class ModuleListTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();

        if (!\defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getFixturesDir());
        }

        $objPage = $this->getMockBuilder(PageModel::class)->disableOriginalConstructor()->getMock();
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

        $finder = new ResourceFinder([
            __DIR__.'/../../vendor/contao/core-bundle/src/Resources/contao',
        ]);

        $containerUtil = new ContainerUtil($framework, $this->createMock(FileLocator::class), $this->createMock(ScopeMatcher::class));
        $modelUtil = new ModelUtil($framework, $containerUtil);

        $container = $this->mockContainer();
        $container->set('request_stack', $requestStack);
        $container->set('router', $router);
        $container->set('contao.framework', $framework);
        $container->set('database_connection', $database);
        $container->set('huh.request', new \HeimrichHannot\RequestBundle\Component\HttpFoundation\Request($this->mockContaoFramework(), $requestStack, $this->mockScopeMatcher()));
        $container->set('huh.list.list-config-registry', $this->createListConfigRegistry());
        $container->set('huh.list.manager.list', new ListManager(
                $framework,
                $container->get('huh.list.list-config-registry'),
                new ListConfigElementRegistry($framework),
                new FilterManager($framework, new FilterSession($framework, new Session(new MockArraySessionStorage()))),
                $container->get('huh.request'),
                $modelUtil,
                new UrlUtil($framework),
                $containerUtil,
                new ImageUtil($framework),
                new FormUtil($container, $framework),
                new \Twig_Environment($this->getMockBuilder('Twig_LoaderInterface')->getMock())
            )
        );

        $container->set('huh.utils.container', $containerUtil);

        $container->setParameter('huh.list', [
            'list' => [
                'managers' => [
                    [
                        'name' => 'default',
                        'id' => 'huh.list.manager.list',
                    ],
                ],
            ],
        ]);
        $container->set('huh.utils.model', $modelUtil);
        $container->set('huh.filter.manager', $this->createFilterManager());
        $container->set('huh.utils.string', new StringUtil($framework));

        $container->set('contao.resource_finder', $finder);
        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.default_locale', 'de');

        $connection = $this->createMock(Connection::class);
        $connection
            ->method('getDatabasePlatform')
            ->willReturn(new MySqlPlatform());

        $connection
            ->expects(!empty($metadata) ? $this->once() : $this->never())
            ->method('getSchemaManager')
            ->willReturn(new MySqlSchemaManager($connection));

        $container->set('database_connection', $connection);

        $kernel = $this->createMock(Kernel::class);
        $kernel->method('getContainer')->willReturn($container);

        System::setContainer($container);
    }

    public function testCanBeInstantiated()
    {
        $model = $this->mockClassWithProperties(ModuleModel::class, ['id' => 1, 'type' => 'list', 'listConfig' => 12]);
        $model->method('row')->willReturn(['id' => 1, 'type' => 'list', 'listConfig' => 12]);

        $module = new ModuleList($model);

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
        $moduleModel = $this->getMockBuilder(ModuleModel::class)->disableOriginalConstructor()->setMethods(['row'])->getMock();
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
        $router->method('generate')->with('contao_backend', $this->anything())->will($this->returnCallback(function ($route, $params = []) {
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
        $modelAdapter = $this->mockAdapter(['getClassFromTable']);

        return [
            Model::class => $modelAdapter,
            System::class => $systemAdapter,
        ];
    }

    public function createListConfigRegistry()
    {
        $listModelData = [
            'id' => 5,
            'filter' => 3,
        ];

        $listRegistryModel = $this->mockClassWithProperties(ListConfigModel::class, $listModelData);
        $listRegistryModel->method('row')->willReturn($listModelData);

        $listRegistryModel->filter = 3;

        $listRegistry = $this->createConfiguredMock(ListConfigRegistry::class, [
            'findByPk' => $listRegistryModel,
            'computeListConfig' => $listRegistryModel,
        ]);

        return $listRegistry;
    }

    public function createFilterManager()
    {
        $filterConfig = $this->getMockBuilder(FilterConfig::class)->disableOriginalConstructor()->setMethods(['getFilter', 'hasData'])->getMock();
        $filterConfig->method('getFilter')->willReturn([
            'dataContainer' => 'tl_news',
        ]);
        $filterConfig->method('hasData')->willReturn(true);

        $filterManagerConfig = [
            'findById' => $filterConfig,
        ];
        $filterManager = $this->createConfiguredMock(FilterManager::class, $filterManagerConfig);

        return $filterManager;
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.\DIRECTORY_SEPARATOR.'..'.\DIRECTORY_SEPARATOR.'Fixtures';
    }

    /**
     * Mocks a request scope matcher.
     *
     * @return ScopeMatcher
     */
    protected function mockScopeMatcher(): ScopeMatcher
    {
        return new ScopeMatcher(
            new RequestMatcher(null, null, null, null, ['_scope' => 'backend']),
            new RequestMatcher(null, null, null, null, ['_scope' => 'frontend'])
        );
    }
}

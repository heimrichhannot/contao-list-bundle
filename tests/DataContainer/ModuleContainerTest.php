<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Test\DataContainer;

use Contao\Controller;
use Contao\DataContainer;
use Contao\Image;
use Contao\ModuleModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ListBundle\DataContainer\ModuleContainer;

class ModuleContainerTest extends ContaoTestCase
{
    public function testGetAllListModules()
    {
        $moduleA = $this->mockClassWithProperties(ModuleModel::class, ['id' => '1', 'name' => 'Module A']);
        $moduleB = $this->mockClassWithProperties(ModuleModel::class, ['id' => '4', 'name' => 'Module B']);

        $moduleModelMock = $this->mockAdapter(['findBy']);
        $moduleModelMock->method('findBy')->willReturnOnConsecutiveCalls(null, [$moduleA, $moduleB]);

        $framework = $this->mockContaoFramework([
            ModuleModel::class => $moduleModelMock,
        ]);
        $moduleContainer = new ModuleContainer($framework);

        $this->assertSame([], $moduleContainer->getAllListModules());
        $this->assertSame(['1' => 'Module A', '4' => 'Module B'], $moduleContainer->getAllListModules());
    }

    public function testEditListConfigurationWizard()
    {
        $framework = $this->mockContaoFramework([
            Controller::class => $this->mockAdapter(['loadLanguageFile']),
            Image::class => $this->mockAdapter(['getHtml']),
        ]);
        $moduleContainer = new ModuleContainer($framework);
        $dataContainer = $this->mockClassWithProperties(DataContainer::class, ['value' => 0]);
        $this->assertEmpty($moduleContainer->editListConfigurationWizard($dataContainer));

        if (!\defined('REQUEST_TOKEN')) {
            \define('REQUEST_TOKEN', 'ABCD');
        }
        $GLOBALS['TL_LANG']['tl_list_config']['edit'] = ['A', 'B'];
        $dataContainer = $this->mockClassWithProperties(DataContainer::class, ['value' => 1]);
        $this->assertStringStartsWith(' <a href="contao?do=list_configs&amp;table=tl_list_config_element&amp;id=1', $moduleContainer->editListConfigurationWizard($dataContainer));
    }
}

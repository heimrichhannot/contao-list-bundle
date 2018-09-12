<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Test\DataContainer;

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
}

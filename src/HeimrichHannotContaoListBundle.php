<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle;

use HeimrichHannot\ListBundle\DependencyInjection\Compiler\ListCompilerPass;
use HeimrichHannot\ListBundle\DependencyInjection\ListExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoListBundle extends Bundle
{
    const ACTION_SHARE = 'share';

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new ListExtension();
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ListCompilerPass());
    }
}

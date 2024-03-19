<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle;

use HeimrichHannot\ListBundle\DependencyInjection\Compiler\ListCompilerPass;
use HeimrichHannot\ListBundle\DependencyInjection\ListExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoListBundle extends Bundle
{
    const ACTION_SHARE = 'share';

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new ListExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ListCompilerPass());
    }
}
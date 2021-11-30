<?php

namespace HeimrichHannot\ListBundle\ListExtension;

use HeimrichHannot\ListBundle\Configuration\ListConfiguration;

interface ListExtensionInterface
{
    public function applyConfiguration(ListConfiguration $listConfiguration): void;

    public function modifyQueryBuilder(): void;
}
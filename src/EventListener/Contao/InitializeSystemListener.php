<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\ListBundle\Asset\FrontendAsset;

/**
 * @Hook("initializeSystem")
 */
class InitializeSystemListener
{
    /**
     * @var FrontendAsset
     */
    protected $frontendAsset;

    public function __construct(FrontendAsset $frontendAsset)
    {
        $this->frontendAsset = $frontendAsset;
    }

    public function __invoke(): void
    {
        $this->frontendAsset->addFrontendAssets();
    }
}

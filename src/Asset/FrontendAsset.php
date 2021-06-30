<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Asset;

use HeimrichHannot\UtilsBundle\Container\ContainerUtil;

class FrontendAsset
{
    /**
     * @var \HeimrichHannot\EncoreBundle\Asset\FrontendAsset
     */
    protected $encoreFrontendAsset;
    /**
     * @var ContainerUtil
     */
    private $containerUtil;

    /**
     * FrontendAsset constructor.
     */
    public function __construct(ContainerUtil $containerUtil)
    {
        $this->containerUtil = $containerUtil;
    }

    public function setEncoreFrontendAsset(\HeimrichHannot\EncoreBundle\Asset\FrontendAsset $encoreFrontendAsset): void
    {
        $this->encoreFrontendAsset = $encoreFrontendAsset;
    }

    public function addFrontendAssets()
    {
        if (!$this->containerUtil->isFrontend()) {
            return;
        }

        if ($this->encoreFrontendAsset) {
            $this->encoreFrontendAsset->addActiveEntrypoint('contao-list-bundle');
        }

        $GLOBALS['TL_JAVASCRIPT']['contao-list-bundle'] = 'bundles/heimrichhannotlistbundle/assets/contao-list-bundle.js|static';
    }
}

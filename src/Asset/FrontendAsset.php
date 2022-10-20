<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Asset;

use HeimrichHannot\EncoreContracts\PageAssetsTrait;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class FrontendAsset implements ServiceSubscriberInterface
{
    use PageAssetsTrait;

    public function addFrontendAssets()
    {
        $this->addPageEntryPoint('contao-multifileupload-bundle', [
            'TL_JAVASCRIPT' => [
                'contao-list-bundle' => 'bundles/heimrichhannotlistbundle/assets/contao-list-bundle.js|static',
            ],
        ]);
    }
}

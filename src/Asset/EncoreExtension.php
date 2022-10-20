<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Asset;

use HeimrichHannot\EncoreContracts\EncoreEntry;
use HeimrichHannot\ListBundle\HeimrichHannotContaoListBundle;

class EncoreExtension implements \HeimrichHannot\EncoreContracts\EncoreExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function getBundle(): string
    {
        return HeimrichHannotContaoListBundle::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getEntries(): array
    {
        return [
            EncoreEntry::create('contao-list-bundle', 'src/Resources/assets/js/contao-list-bundle.js')
                ->setRequiresCss(false)
                ->addJsEntryToRemoveFromGlobals('contao-list-bundle')
                ->addJsEntryToRemoveFromGlobals('huh_components_masonry')
                ->addJsEntryToRemoveFromGlobals('huh_components_imagesloaded')
                ->addJsEntryToRemoveFromGlobals('huh_components_jscroll'),
        ];
    }
}

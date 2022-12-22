<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\EventListener\Contao;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\ListBundle\ListExtension\ListExtensionCollection;
use HeimrichHannot\ListBundle\Model\ListConfigModel;

/**
 * @Hook("loadDataContainer")
 */
class LoadDataContainerListener
{
    private ListExtensionCollection $listExtensionCollection;

    public function __construct(ListExtensionCollection $listExtensionCollection)
    {
        $this->listExtensionCollection = $listExtensionCollection;
    }

    public function __invoke(string $table): void
    {
        if (ListConfigModel::getTable() !== $table) {
            return;
        }

        if (empty($this->listExtensionCollection->getExtensions())) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA'][$table];
        $paletteManipulator = PaletteManipulator::create();

        foreach ($this->listExtensionCollection->getExtensions() as $extension) {
            $fieldname = 'use'.ucfirst($extension::getAlias());

            if (isset($dca['fields'][$fieldname])) {
                continue;
            }

            $dca['fields'][$fieldname] = [
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => ['tl_class' => 'w50'],
                'sql' => "char(1) NOT NULL default ''",
            ];

            $fields = $extension::getFields();

            if (!empty($fields)) {
                $dca['fields'][$fieldname]['eval']['submitOnChange'] = true;
                $dca['subpalettes'][$fieldname] = implode(',', $fields);
                $dca['palettes']['__selector__'][] = $fieldname;
            }

            $paletteManipulator->addField($fieldname, 'extension_legend', PaletteManipulator::POSITION_APPEND);
        }

        $paletteManipulator->applyToPalette('default', ListConfigModel::getTable());
    }
}

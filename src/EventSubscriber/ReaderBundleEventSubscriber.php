<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\EventSubscriber;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\Database;
use Contao\ModuleModel;
use HeimrichHannot\ListBundle\Controller\FrontendModule\ListFrontendModuleController;
use HeimrichHannot\ReaderBundle\Module\ModuleReader;

// todo: replace module reader with any replacement that does it

class ReaderBundleEventSubscriber
{
    public function onLoadDataContainer(string $table): void
    {
        if (!class_exists(ModuleReader::class)
            || $table !== ModuleModel::getTable())
        {
            return;
        }

        // Add option to hide list module and render reader module on detail pages
        $dca = &$GLOBALS['TL_DCA'][ModuleModel::getTable()];
        $dca['fields']['list_renderReaderOnAutoItem'] = [
            'label' => &$GLOBALS['TL_LANG']['tl_module']['list_renderReaderOnAutoItem'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 clr', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ];
        $dca['fields']['list_readerModule'] = [
            'label' => &$GLOBALS['TL_LANG']['tl_module']['list_readerModule'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => static function () {
                $arrModules = [];
                $objModules = Database::getInstance()->execute(
                    "SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id WHERE m.type='".ModuleReader::TYPE."' ORDER BY t.name, m.name"
                );

                while ($objModules->next()) {
                    $arrModules[$objModules->theme][$objModules->id] = $objModules->name.' (ID '.$objModules->id.')';
                }

                return $arrModules;
            },
            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'addAsDataAttribute' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ];
        $dca['subpalettes']['list_renderReaderOnAutoItem'] = 'list_readerModule';
        $dca['palettes']['__selector__'][] = 'list_renderReaderOnAutoItem';
        PaletteManipulator::create()
            ->addField('list_renderReaderOnAutoItem', 'listConfig', PaletteManipulator::POSITION_AFTER)
            ->applyToPalette(ListFrontendModuleController::TYPE, ModuleModel::getTable())
        ;
    }
}

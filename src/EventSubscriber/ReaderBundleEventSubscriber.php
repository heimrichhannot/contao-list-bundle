<?php

namespace HeimrichHannot\ListBundle\EventSubscriber;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\Database;
use Contao\ModuleModel;
use HeimrichHannot\ListBundle\Module\ModuleList;
use HeimrichHannot\ReaderBundle\Module\ModuleReader;

class ReaderBundleEventSubscriber
{
    public function onLoadDataContainer(string $table)
    {
        if (class_exists('HeimrichHannot\ReaderBundle\HeimrichHannotContaoReaderBundle') && $table === ModuleModel::getTable()) {
            // Add option to hide list module and render reader module on detail pages
            $dca = &$GLOBALS['TL_DCA'][ModuleModel::getTable()];
            $dca['fields']['list_renderReaderOnAutoItem'] =[
                'label' => &$GLOBALS['TL_LANG']['tl_module']['list_renderReaderOnAutoItem'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => ['tl_class' => 'w50 clr', 'submitOnChange' => true],
                'sql' => "char(1) NOT NULL default ''",
            ];
            $dca['fields']['list_readerModule'] =[
                'label' => &$GLOBALS['TL_LANG']['tl_module']['list_readerModule'],
                'exclude' => true,
                'filter' => true,
                'inputType' => 'select',
                'options_callback' => static function () {
                    $arrModules = array();
                    $objModules = Database::getInstance()->execute(
                        "SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id WHERE m.type='".ModuleReader::TYPE."' ORDER BY t.name, m.name"
                    );
                    while ($objModules->next())
                    {
                        $arrModules[$objModules->theme][$objModules->id] = $objModules->name . ' (ID ' . $objModules->id . ')';
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
                ->applyToPalette(ModuleList::TYPE, ModuleModel::getTable())
            ;
        }
    }
}
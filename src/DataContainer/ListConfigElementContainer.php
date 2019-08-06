<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\ListBundle\DataContainer;


use Contao\BackendUser;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\DC_Table;
use Contao\System;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;
use HeimrichHannot\ListBundle\Registry\ListConfigElementRegistry;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ListConfigElementContainer
{
    /**
     * @var ListConfigElementRegistry
     */
    private $configElementRegistry;
    /**
     * @var ContainerInterface
     */
    private $container;


    /**
     * ListConfigElementContainer constructor.
     */
    public function __construct(ListConfigElementRegistry $configElementRegistry, ContainerInterface $container)
    {
        $this->configElementRegistry = $configElementRegistry;
        $this->container = $container;
    }

    /**
     * Return a list of config element types for dca
     *
     * @return array
     */
    public function getConfigElementTypes(DC_Table $dcTable)
    {
        $types = array_keys($this->configElementRegistry->getConfigElementTypes());

        // TODO: remove in next major version
        $listConfig = $this->container->getParameter('huh.list');
        $configElementTypes = $listConfig['list']['config_element_types'];
        foreach ($configElementTypes as $configElementType) {
            if (in_array($configElementType['name'], $types)) {
                continue;
            }
            $types[] = $configElementType['name'];
        }

        return $types;
    }

    public function onLoadCallback($dcTable)
    {
        $configElementTypes = $this->configElementRegistry->getConfigElementTypes();
        if (empty($configElementTypes)) {
            return;
        }

        foreach ($configElementTypes as $listConfigElementType) {
            $palette = '{title_type_legend},title,type,templateVariable;'.$listConfigElementType->getPalette();
            $GLOBALS['TL_DCA'][ListConfigElementModel::getTable()]['palettes'][$listConfigElementType::getType()] = $palette;
        }
        return;
    }

    public function listChildren($rows)
    {
        $reference = $GLOBALS['TL_DCA']['tl_list_config_element']['fields']['type']['reference'];
        return '<div class="tl_content_left">'.($rows['title'] ?: $rows['id']).' <span style="color:#b3b3b3; padding-left:3px">['
            .$reference[$rows['type']].'] ('
            .\Date::parse(\Contao\Config::get('datimFormat'), trim($rows['dateAdded'])).')</span></div>';
    }
}
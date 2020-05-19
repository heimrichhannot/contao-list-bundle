<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\DataContainer;

use Contao\Config;
use Contao\Date;
use Contao\DC_Table;
use Contao\StringUtil;
use HeimrichHannot\ListBundle\ConfigElementType\RelatedConfigElementType;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;
use HeimrichHannot\ListBundle\Registry\ListConfigElementRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ListConfigElementContainer
{
    const RELATED_CRITERIUM_TAGS = 'tags';
    const RELATED_CRITERIUM_CATEGORIES = 'categories';

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
     * Return a list of config element types for dca.
     *
     * @return array
     */
    public function getConfigElementTypes(DC_Table $dc)
    {
        $types = array_keys($this->configElementRegistry->getConfigElementTypes());

        // TODO: remove in next major version
        $listConfig = $this->container->getParameter('huh.list');
        $configElementTypes = $listConfig['list']['config_element_types'];

        foreach ($configElementTypes as $configElementType) {
            if (\in_array($configElementType['name'], $types)) {
                continue;
            }
            $types[] = $configElementType['name'];
        }

        return $types;
    }

    public function getRelatedCriteriaAsOptions()
    {
        $options = [];

        if (class_exists('\Codefog\TagsBundle\CodefogTagsBundle')) {
            $options[] = static::RELATED_CRITERIUM_TAGS;
        }

        // TODO
//        if (class_exists('\HeimrichHannot\CategoriesBundle\CategoriesBundle')) {
//            $options[] = static::RELATED_CRITERIUM_CATEGORIES;
//        }

        return $options;
    }

    public function onLoadCallback($dc)
    {
        if (null === ($listConfigElement = $this->container->get('huh.utils.model')->findModelInstanceByPk('tl_list_config_element', $dc->id))) {
            return;
        }

        $configElementTypes = $this->configElementRegistry->getConfigElementTypes();

        if (empty($configElementTypes)) {
            return;
        }

        foreach ($configElementTypes as $listConfigElementType) {
            $palette = '{title_type_legend},title,type,templateVariable;'.$listConfigElementType->getPalette();
            $GLOBALS['TL_DCA'][ListConfigElementModel::getTable()]['palettes'][$listConfigElementType::getType()] = $palette;
        }

        // related
        if ($listConfigElement->type === RelatedConfigElementType::getType()) {
            $criteria = StringUtil::deserialize($listConfigElement->relatedCriteria, true);

            if (\in_array(static::RELATED_CRITERIUM_TAGS, $criteria)) {
                $GLOBALS['TL_DCA']['tl_list_config_element']['palettes'][RelatedConfigElementType::getType()] = str_replace(
                    'relatedCriteria;', 'relatedCriteria,tagsField;',
                    $GLOBALS['TL_DCA']['tl_list_config_element']['palettes'][RelatedConfigElementType::getType()]
                );
            }
        }
    }

    public function listChildren($rows)
    {
        $reference = $GLOBALS['TL_DCA']['tl_list_config_element']['fields']['type']['reference'];

        return '<div class="tl_content_left">'.($rows['title'] ?: $rows['id']).' <span style="color:#b3b3b3; padding-left:3px">['
            .$reference[$rows['type']].'] ('
            .Date::parse(Config::get('datimFormat'), trim($rows['dateAdded'])).')</span></div>';
    }
}

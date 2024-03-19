<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\DataContainer;

use Contao\Config;
use Contao\Date;
use Contao\DC_Table;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ConfigElementTypeBundle\ConfigElementType\ConfigElementTypeInterface;
use HeimrichHannot\ListBundle\ConfigElementType\ListConfigElementTypeInterface;
use HeimrichHannot\ListBundle\ConfigElementType\RelatedConfigElementType;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;
use HeimrichHannot\ListBundle\Registry\ListConfigElementRegistry;
use HeimrichHannot\UtilsBundle\Util\Utils;

class ListConfigElementContainer
{
    const PREPEND_PALETTE = '{title_type_legend},title,type,templateVariable;';
    const APPEND_PALETTE = '';

    const RELATED_CRITERION_TAGS = 'tags';
    const RELATED_CRITERION_CATEGORIES = 'categories';
    /** @deprecated Use {@see ListConfigElementContainer::RELATED_CRITERION_TAGS} instead */
    const RELATED_CRITERIUM_TAGS = self::RELATED_CRITERION_TAGS;
    /** @deprecated Use {@see ListConfigElementContainer::RELATED_CRITERION_CATEGORIES} instead */
    const RELATED_CRITERIUM_CATEGORIES = self::RELATED_CRITERION_CATEGORIES;

    /**
     * @var ListConfigElementRegistry
     */
    private $configElementRegistry;
    protected Utils $utils;

    /**
     * ListConfigElementContainer constructor.
     */
    public function __construct(
        ListConfigElementRegistry $configElementRegistry,
        Utils $utils
    ) {
        $this->configElementRegistry = $configElementRegistry;
        $this->utils = $utils;
    }

    /**
     * Return a list of config element types for dca.
     *
     * @return array
     */
    public function getConfigElementTypes(DC_Table $dc): array
    {
        $types = array_keys($this->configElementRegistry->getConfigElementTypes());

        // TODO: remove in next major version
        $listConfig = System::getContainer()->getParameter('huh.list');
        $configElementTypes = $listConfig['list']['config_element_types'];

        foreach ($configElementTypes as $configElementType) {
            if (in_array($configElementType['name'], $types)) {
                continue;
            }
            $types[] = $configElementType['name'];
        }

        return $types;
    }

    public function getRelatedCriteriaAsOptions(): array
    {
        $options = [];

        if (class_exists('\Codefog\TagsBundle\CodefogTagsBundle')) {
            $options[] = static::RELATED_CRITERION_TAGS;
        }

        if (class_exists('\HeimrichHannot\CategoriesBundle\CategoriesBundle')) {
            $options[] = static::RELATED_CRITERION_CATEGORIES;
        }

        return $options;
    }

    public function onLoadCallback($dc): void
    {
        /** @var ListConfigElementModel $listConfigElement */
        $listConfigElement = $this->utils->model()->findModelInstanceByPk('tl_list_config_element', $dc->id);
        if ($listConfigElement === null) {
            return;
        }

        $configElementTypes = $this->configElementRegistry->getConfigElementTypes();
        if (empty($configElementTypes)) {
            return;
        }

        foreach ($configElementTypes as $listConfigElementType) {
            if ($listConfigElementType instanceof ConfigElementTypeInterface) {
                /** @var ConfigElementTypeInterface $listConfigElementType */
                $palette = $listConfigElementType->getPalette(static::PREPEND_PALETTE, static::APPEND_PALETTE);
            } else {
                /** @var ListConfigElementTypeInterface $listConfigElementType */
                $palette = static::PREPEND_PALETTE.$listConfigElementType->getPalette().static::APPEND_PALETTE;
            }

            $GLOBALS['TL_DCA'][ListConfigElementModel::getTable()]['palettes'][$listConfigElementType::getType()] = $palette;
        }

        $listConfigElementType = $this->configElementRegistry->getListConfigElementType($listConfigElement->type);
        if ($listConfigElementType instanceof ConfigElementTypeInterface) {
            $GLOBALS['TL_DCA'][ListConfigElementModel::getTable()]['fields']['templateVariable']['eval']['mandatory'] = true;
        }

        // related
        if ($listConfigElement->type === RelatedConfigElementType::getType()) {
            $criteria = StringUtil::deserialize($listConfigElement->relatedCriteria, true);

            $fields = [];

            if (in_array(static::RELATED_CRITERION_TAGS, $criteria)) {
                $fields[] = 'tagsField';
            }

            if (in_array(static::RELATED_CRITERION_CATEGORIES, $criteria)) {
                $fields[] = 'categoriesField';
            }

            $GLOBALS['TL_DCA']['tl_list_config_element']['palettes'][RelatedConfigElementType::getType()] = str_replace(
                'relatedCriteria;', 'relatedCriteria,'.implode(',', $fields).';',
                $GLOBALS['TL_DCA']['tl_list_config_element']['palettes'][RelatedConfigElementType::getType()]
            );
        }
    }

    public function listChildren($rows): string
    {
        $reference = $GLOBALS['TL_DCA']['tl_list_config_element']['fields']['type']['reference'];

        return '<div class="tl_content_left">' . ($rows['title'] ?: $rows['id']) . ' <span style="color:#b3b3b3; padding-left:3px">['
            . $reference[$rows['type']] . '] ('
            . Date::parse(Config::get('datimFormat'), trim($rows['dateAdded'])) . ')</span></div>';
    }
}
<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ConfigElementType;

use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Database;
use Contao\Input;
use Contao\Model;
use Contao\System;
use HeimrichHannot\FilterBundle\Util\TwigSupportPolyfill\TwigTemplateLocator;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment as TwigEnvironment;

class TagsConfigElementType implements ListConfigElementTypeInterface
{
    protected TwigEnvironment $twig;
    protected TwigTemplateLocator $templateLocator;
    protected Utils $utils;
    protected RequestStack $requestStack;

    public function __construct(
        TwigEnvironment $twig,
        TwigTemplateLocator $templateLocator,
        Utils $utils,
        RequestStack $requestStack
    ) {
        $this->twig = $twig;
        $this->templateLocator = $templateLocator;
        $this->utils = $utils;
        $this->requestStack = $requestStack;
    }

    public function renderTags($configElement, $item): ?string
    {
        $table = $item->getDataContainer();

        if (!$table || !isset($GLOBALS['TL_DCA'][$table]['fields'][$configElement->tagsField]['eval']['tagsManager']) || !$configElement->tagsField) {
            return '';
        }

        if (empty($GLOBALS['TL_DCA'][$table])) {
            Controller::loadDataContainer($table);
        }

        $source = $GLOBALS['TL_DCA'][$table]['fields'][$configElement->tagsField]['eval']['tagsManager'];

        $nonTlTable = str_starts_with($table, 'tl_') ? substr($table, 3) : $table;
        $cfgTable = 'tl_cfg_tag_'.$nonTlTable;

        $tags = [];

        $tagRecords = Database::getInstance()->prepare("SELECT t.* FROM tl_cfg_tag t INNER JOIN $cfgTable t2 ON t.id = t2.cfg_tag_id".
            " WHERE t2.{$nonTlTable}_id=? AND t.source=? ORDER BY t.name")->execute(
            $item->getRawValue('id'),
            $source
        );

        if ($tagRecords->numRows > 0) {
            $tags = $tagRecords->fetchAllAssoc();
        }

        if ($configElement->tagsAddLink)
        {
            $tagId = Input::get('huh_cfg_tag');

            $jumpTo = $configElement->tagsJumpTo;
            if ($jumpTo && $jumpTo != $GLOBALS['objPage']?->id || $jumpTo = null)
            {
                $jumpToPage = $this->utils->model()->findModelInstanceByPk('tl_page', $jumpTo);
                $jumpTo = $jumpToPage instanceof Model ? $jumpToPage->getFrontendUrl() : null;
            }

            /**
             * Defer fetching the filter config element from the database until we know we need it.
             *
             * This closure can only be called once since it overwrites the pointer that was previously pointed to it.
             * This shenanigan is probably not a good practice, but in this very instance, it's convenient and fun, and
             * it's contained in a narrow scope. This is fine.
             *
             * @return Model|null
             */
            $filterConfigElement = function () use ($configElement, &$filterConfigElement) {
                return $filterConfigElement = $this->utils->model()
                    ->findModelInstanceByPk('tl_filter_config_element', $configElement->tagsFilterConfigElement);
            };

            if ($tagId && $jumpTo && $filterConfigElement() !== null)
            {
                /** @var Model|null $filterConfigElement */
                $sessionKey = System::getContainer()->get('huh.filter.manager')->findById($configElement->tagsFilter)->getSessionKey();
                $sessionData = System::getContainer()->get('huh.filter.session')->getData($sessionKey);
                $sessionData[$filterConfigElement->field] = $tagId;

                System::getContainer()->get('huh.filter.session')->setData($sessionKey, $sessionData);

                throw new RedirectResponseException('/'.ltrim($jumpTo, '/'), 301);
            }

            foreach ($tags as &$tag) {
                $tag['url'] = $this->utils->url()->addQueryStringParameterToUrl('huh_cfg_tag='.$tag['id']);
            }
        }

        $data = [
            'configElement' => $configElement,
            'item' => $item,
        ];

        $data['tags'] = $tags;

        return $this->twig->render($this->templateLocator->getTemplatePath($configElement->tagsTemplate), $data);
    }

    /**
     * Return the config element type alias.
     */
    public static function getType(): string
    {
        return 'tags';
    }

    /**
     * Return the config element type palette.
     */
    public function getPalette(): string
    {
        return '{config_legend},tagsField,tagsAddLink,tagsTemplate;';
    }

    /**
     * Update the item data.
     */
    public function addToListItemData(ListConfigElementData $configElementData): void
    {
        $listConfigElement = $configElementData->getListConfigElement();
        $item = $configElementData->getItem();

        $item->setFormattedValue(
            $listConfigElement->templateVariable ?: 'tags',
            $this->renderTags($listConfigElement, $item)
        );

        $configElementData->setItem($item);
    }
}
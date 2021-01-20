<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ConfigElementType;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Database;
use Contao\System;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use Twig\Environment;

class TagsConfigElementType implements ListConfigElementTypeInterface
{
    /**
     * @var TwigTemplateLocator
     */
    protected $templateLocator;
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var StringUtil
     */
    private $stringUtil;
    /**
     * @var DcaUtil
     */
    private $dcaUtil;
    /**
     * @var UrlUtil
     */
    private $urlUtil;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ModelUtil
     */
    private $modelUtil;

    public function __construct(Environment $twig, StringUtil $stringUtil, DcaUtil $dcaUtil, UrlUtil $urlUtil, Request $request, ModelUtil $modelUtil, TwigTemplateLocator $templateLocator)
    {
        $this->twig = $twig;
        $this->stringUtil = $stringUtil;
        $this->dcaUtil = $dcaUtil;
        $this->urlUtil = $urlUtil;
        $this->request = $request;
        $this->modelUtil = $modelUtil;
        $this->templateLocator = $templateLocator;
    }

    public function renderTags($configElement, $item): ?string
    {
        $table = $item->getDataContainer();

        if (!$table || !isset($GLOBALS['TL_DCA'][$table]['fields'][$configElement->tagsField]['eval']['tagsManager']) || !$configElement->tagsField) {
            return '';
        }

        $this->dcaUtil->loadDc($table);

        $source = $GLOBALS['TL_DCA'][$table]['fields'][$configElement->tagsField]['eval']['tagsManager'];

        $nonTlTable = $this->stringUtil->removeLeadingString('tl_', $item->getDataContainer());
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

        if ($configElement->tagsAddLink) {
            $jumpTo = $this->urlUtil->getJumpToPageUrl($configElement->tagsJumpTo, false);

            if (($tagId = $this->request->getGet('huh_cfg_tag')) && $jumpTo) {
                if (null !== ($filterConfigElement = $this->modelUtil->findModelInstanceByPk('tl_filter_config_element', $configElement->tagsFilterConfigElement))) {
                    $sessionKey = System::getContainer()->get('huh.filter.manager')->findById($configElement->tagsFilter)->getSessionKey();

                    $sessionData = System::getContainer()->get('huh.filter.session')->getData($sessionKey);

                    $sessionData = \is_array($sessionData) ? $sessionData : [];

                    $sessionData[$filterConfigElement->field] = $tagId;

                    System::getContainer()->get('huh.filter.session')->setData($sessionKey, $sessionData);

                    throw new RedirectResponseException('/'.ltrim($jumpTo, '/'), 301);
                }
            }

            foreach ($tags as &$tag) {
                $tag['url'] = $this->urlUtil->addQueryString('huh_cfg_tag='.$tag['id']);
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

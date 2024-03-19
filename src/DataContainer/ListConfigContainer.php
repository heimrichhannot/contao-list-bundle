<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use HeimrichHannot\FilterBundle\Util\TwigSupportPolyfill\TwigTemplateLocator;
use HeimrichHannot\ListBundle\Choice\ListChoices;
use HeimrichHannot\ListBundle\Util\Polyfill;
use HeimrichHannot\UtilsBundle\Util\Utils;

class ListConfigContainer
{
    protected array $bundleConfig;
    protected TwigTemplateLocator $templateLocator;
    protected Utils $utils;
    protected ContaoFramework $framework;
    protected ContaoCsrfTokenManager $csrfTokenManager;

    /**
     * ListConfigContainer constructor.
     */
    public function __construct(
        array $bundleConfig,
        TwigTemplateLocator $templateLocator,
        Utils $utils,
        ContaoFramework $framework,
        ContaoCsrfTokenManager $csrfTokenManager
    ) {
        $this->bundleConfig = $bundleConfig;
        $this->templateLocator = $templateLocator;
        $this->utils = $utils;
        $this->framework = $framework;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function onItemTemplateOptionsCallback(): array
    {
        return $this->getTemplateChoices('item_prefixes', 'item');
    }

    public function onListTemplateOptionsCallback(): array
    {
        return $this->getTemplateChoices('list_prefixes', 'list');
    }

    public function onItemChoiceTemplateOptionsCallback(): array
    {
        return $this->getTemplateChoices('item_choice_prefixes', 'item_choice');
    }

    public function sortAlphabetically(): string
    {
        $anchor = sprintf('<a href="%s" class="header_new" style="background-image: url(%s)" title="%s" accesskey="n" onclick="Backend.getScrollOffset();return !!confirm(\'%s\');">%s</a>',
            $this->utils->url()->addQueryStringParameterToUrl('key=sortAlphabetically'),
            'system/themes/flexible/icons/rows.svg',
            $GLOBALS['TL_LANG']['tl_list_config']['sortAlphabetically'][1],
            $GLOBALS['TL_LANG']['tl_list_config']['reference']['sortAlphabeticallyConfirm'],
            $GLOBALS['TL_LANG']['tl_list_config']['sortAlphabetically'][0]
        );

        if ('sortAlphabetically' !== Input::get('key')) {
            return $anchor;
        }

        $listConfigClass = $this->framework->getAdapter(Model::class)
            ?->getClassFromTable('tl_list_config');
        /** @var Adapter<Model> $adapter */
        $adapter = $listConfigClass ? $this->framework->getAdapter($listConfigClass) : null;
        $listConfigs = $adapter?->findAll(['order' => 'title ASC']);

        if ($listConfigs !== null)
        {
            $sorting = 64;

            while ($listConfigs->next()) {
                $sorting += 64;

                $listConfig = $listConfigs->current();

                // The sorting has not changed
                if ($sorting == $listConfig->sorting) {
                    continue;
                }

                // Initialize the version manager
                $versions = new Versions('tl_list_config', $listConfig->id);
                $versions->initialize();

                // Store the new alias
                Database::getInstance()->prepare('UPDATE tl_list_config SET sorting=? WHERE id=?')
                    ->execute($sorting, $listConfig->id);

                // Create a new version
                $versions->create();
            }
        }

        throw new RedirectResponseException($this->utils->url()->removeQueryStringParameterFromUrl('key'));
    }

    public function pasteListConfig(DataContainer $dc, $row, $table, $cr, $arrClipboard = null): string
    {
        $disablePA = false;
        $disablePI = false;

        // Disable all buttons if there is a circular reference
        if (false !== $arrClipboard && ('cut' === $arrClipboard['mode'] && (1 === $cr || $arrClipboard['id'] === $row['id']) || 'cutAll' === $arrClipboard['mode'] && (1 === $cr || \in_array($row['id'], $arrClipboard['id'], true)))) {
            $disablePA = true;
            $disablePI = true;
        }

        $return = '';

        // Return the buttons
        $pasteafter = sprintf($GLOBALS['TL_LANG']['DCA']['pasteafter'][1], $row['id']);
        $pasteinto = sprintf($GLOBALS['TL_LANG']['DCA']['pasteinto'][1], $row['id']);
        
        $imagePasteAfter = Image::getHtml('pasteafter.svg', $pasteafter);
        $imagePasteInto = Image::getHtml('pasteinto.svg', $pasteinto);

        $anchorTemplate = '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>';

        $requestToken = $this->csrfTokenManager->getDefaultTokenValue();

        if ($row['id'] > 0)
        {
            $return = $disablePA
                ? Image::getHtml('pasteafter_.svg')
                : sprintf(
                    $anchorTemplate,
                    Controller::addToUrl(
                        'act=' . $arrClipboard['mode']
                        . '&mode=1&rt=' . $requestToken
                        . '&pid=' . $row['id']
                        . (!is_array($arrClipboard['id']) ? '&id=' . $arrClipboard['id'] : '')
                    ),
                    StringUtil::specialchars($pasteafter),
                    $imagePasteAfter
                );
        }

        $return .= $disablePI
            ? Image::getHtml('pasteinto_.svg') . ' '
            : sprintf(
                $anchorTemplate,
                Controller::addToUrl(
                    'act=' . $arrClipboard['mode']
                    . '&mode=2&rt=' . $requestToken
                    . '&pid=' . $row['id']
                    . (!is_array($arrClipboard['id']) ? '&id=' . $arrClipboard['id'] : '')
                ),
                StringUtil::specialchars($pasteinto),
                $imagePasteInto
            );

        return $return;
    }

    protected function getTemplateChoices(string $prefixesKey, string $templatesKey): array
    {
        $choices = [];

        if (isset($this->bundleConfig['templates'][$prefixesKey])) {
            $choices = $this->templateLocator->getTemplateGroup($this->bundleConfig['templates'][$prefixesKey]);
        }

        if (isset($this->bundleConfig['templates'][$templatesKey])) {
            $templates = array_column($this->bundleConfig['templates'][$templatesKey], 'template');

            foreach ($choices as $key => $choice) {
                $templatePath = $this->templateLocator->getTemplatePath($key);

                // remove duplicates
                if (false !== array_search($templatePath, $templates)) {
                    unset($choices[$key]);
                }
            }

            foreach ($this->bundleConfig['templates'][$templatesKey] as $template) {
                $choices[$template['name']] = $template['template'].' (Yaml)';
            }
        }

        asort($choices);

        return $choices;
    }

    public static function getModelInstances(DataContainer $dc)
    {
        $listConfigRegistry = System::getContainer()->get('huh.list.list-config-registry');
        $listConfig = $listConfigRegistry->findByPk($dc->id);

        if (null === $listConfig) {
            return [];
        }

        $modelUtil = System::getContainer()->get(Utils::class)->model();
        $listConfig = Polyfill::findRootParentRecursively($modelUtil, 'pid', 'tl_list_config', $listConfig);

        if (null === $listConfig) {
            return [];
        }

        $filter = $listConfigRegistry->getFilterByPk($listConfig->id);

        if (null === $filter) {
            return [];
        }

        $dc->table = $filter['dataContainer'] ?? $dc->table;

        return ListChoices::getModelInstanceOptions($dc, $filter['dataContainer'] ?? $dc->table);
    }

    public static function getFields(DataContainer $dc)
    {
        $listConfigRegistry = System::getContainer()->get('huh.list.list-config-registry');

        if (null === ($listConfig = $listConfigRegistry->findByPk($dc->id))) {
            return [];
        }

        $modelUtil = System::getContainer()->get(Utils::class)->model();
        $listConfig = Polyfill::findRootParentRecursively($modelUtil, 'pid', 'tl_list_config', $listConfig);

        if (null === $listConfig || null === ($filter = $listConfigRegistry->getFilterByPk($listConfig->id))) {
            return [];
        }

        return System::getContainer()->get(Utils::class)->dca()->getDcaFields($filter['dataContainer']);
    }
}

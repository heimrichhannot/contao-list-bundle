<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\RequestToken;
use Contao\StringUtil;
use Contao\Versions;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;

class ListConfigContainer
{
    /**
     * @var array
     */
    protected $bundleConfig;
    /**
     * @var TwigTemplateLocator
     */
    protected $templateLocator;
    /**
     * @var ModelUtil
     */
    protected $modelUtil;
    /**
     * @var UrlUtil
     */
    protected $urlUtil;
    /**
     * @var Request
     */
    protected $request;

    /**
     * ListConfigContainer constructor.
     */
    public function __construct(
        array $bundleConfig,
        TwigTemplateLocator $templateLocator,
        ModelUtil $modelUtil,
        UrlUtil $urlUtil,
        Request $request
    ) {
        $this->bundleConfig = $bundleConfig;
        $this->templateLocator = $templateLocator;
        $this->modelUtil = $modelUtil;
        $this->urlUtil = $urlUtil;
        $this->request = $request;
    }

    public function onItemTemplateOptionsCallback()
    {
        return $this->getTemplateChoices('item_prefixes', 'item');
    }

    public function onListTemplateOptionsCallback()
    {
        return $this->getTemplateChoices('list_prefixes', 'list');
    }

    public function onItemChoiceTemplateOptionsCallback()
    {
        return $this->getTemplateChoices('item_choice_prefixes', 'item_choice');
    }

    public function sortAlphabetically()
    {
        // sort alphabetically
        if ('sortAlphabetically' === $this->request->getGet('key')) {
            if (null !== ($listConfigs = $this->modelUtil->findAllModelInstances('tl_list_config', [
                    'order' => 'title ASC',
                ]))) {
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

            throw new RedirectResponseException($this->urlUtil->removeQueryString(['key']));
        }

        return '<a href="'.$this->urlUtil->addQueryString('key=sortAlphabetically').'" class="header_new" style="background-image: url(system/themes/flexible/icons/rows.svg)" title="'.$GLOBALS['TL_LANG']['tl_list_config']['sortAlphabetically'][1].'" accesskey="n" onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['tl_list_config']['reference']['sortAlphabeticallyConfirm'].'\'))return false;Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['tl_list_config']['sortAlphabetically'][0].'</a>';
    }

    public function pasteListConfig(DataContainer $dc, $row, $table, $cr, $arrClipboard = null)
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

        if ($row['id'] > 0) {
            $return = $disablePA ? Image::getHtml('pasteafter_.svg').' ' : '<a href="'.Controller::addToUrl('act='.$arrClipboard['mode'].'&mode=1&rt='.RequestToken::get().'&pid='.$row['id'].(!\is_array($arrClipboard['id']) ? '&id='.$arrClipboard['id'] : '')).'" title="'.StringUtil::specialchars($pasteafter).'" onclick="Backend.getScrollOffset()">'.$imagePasteAfter.'</a> ';
        }

        return $return.($disablePI ? Image::getHtml('pasteinto_.svg').' ' : '<a href="'.Controller::addToUrl('act='.$arrClipboard['mode'].'&mode=2&rt='.RequestToken::get().'&pid='.$row['id'].(!\is_array($arrClipboard['id']) ? '&id='.$arrClipboard['id'] : '')).'" title="'.StringUtil::specialchars($pasteinto).'" onclick="Backend.getScrollOffset()">'.$imagePasteInto.'</a> ');
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
}

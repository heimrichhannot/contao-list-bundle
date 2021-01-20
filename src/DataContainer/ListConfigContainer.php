<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\DataContainer;

use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;

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
     * ListConfigContainer constructor.
     */
    public function __construct(array $bundleConfig, TwigTemplateLocator $templateLocator)
    {
        $this->bundleConfig = $bundleConfig;
        $this->templateLocator = $templateLocator;
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

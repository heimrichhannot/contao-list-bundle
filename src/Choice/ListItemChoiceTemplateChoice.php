<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Choice;

use Contao\System;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class ListItemChoiceTemplateChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $config = System::getContainer()->getParameter('huh.list');

        if (isset($config['list']['templates']['item_choice_prefixes'])) {
            $choices = System::getContainer()->get('huh.utils.choice.twig_template')->setContext($config['list']['templates']['item_choice_prefixes'])->getCachedChoices();
        }

        if (isset($config['list']['templates']['item_choice'])) {
            foreach ($config['list']['templates']['item_choice'] as $template) {
                $templateName = $template['template'].' (Yaml)';
                // remove duplicates returned by `huh.utils.choice.twig_template`
                if (false !== ($idx = array_search($template['template'], $choices))) {
                    unset($choices[$idx]);
                }

                if (false !== ($idx = array_search($templateName, $choices))) {
                    unset($choices[$idx]);
                }
                $choices[$template['name']] = $templateName;
            }
        }

        asort($choices);

        return $choices;
    }
}

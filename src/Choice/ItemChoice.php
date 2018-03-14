<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Choice;

use Contao\System;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class ItemChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $config = System::getContainer()->getParameter('huh.list');

        if (!isset($config['list']['items'])) {
            return $choices;
        }

        foreach ($config['list']['items'] as $manager) {
            $choices[$manager['name']] = $manager['class'];
        }

        asort($choices);

        return $choices;
    }
}

<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Choice;

use Contao\System;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class ManagerChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $config = System::getContainer()->getParameter('huh.list');

        if (!isset($config['list']['managers'])) {
            return $choices;
        }

        foreach ($config['list']['managers'] as $manager) {
            $choices[$manager['name']] = $manager['id'];
        }

        asort($choices);

        return $choices;
    }
}

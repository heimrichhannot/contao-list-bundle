<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Choice;

use Contao\System;
use HeimrichHannot\FilterBundle\Util\AbstractChoice;

class ListChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect(): array
    {
        $choices = [];

        $config = System::getContainer()->getParameter('huh.list');

        if (!isset($config['list']['lists'])) {
            return $choices;
        }

        foreach ($config['list']['lists'] as $manager) {
            $choices[$manager['name']] = $manager['class'];
        }

        asort($choices);

        return $choices;
    }
}

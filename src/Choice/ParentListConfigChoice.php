<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Choice;

use Contao\System;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class ParentListConfigChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $id = $this->getContext()['id'];

        if (!$id
            || null === ($listConfigs = System::getContainer()->get('huh.list.list-config-registry')->findBy(
                [
                    'tl_list_config.id != ?',
                ],
                [
                    $id,
                ]
            ))
        ) {
            return [];
        }

        $choices = array_combine(
            $listConfigs->fetchEach('id'),
            $listConfigs->fetchEach('title')
        );

        asort($choices);

        return $choices;
    }
}

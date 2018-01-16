<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\ListBundle\Util;

use Contao\Config;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;

class Helper
{
    public static function shareTokenExpiredOrEmpty($entity, $now)
    {
        $shareToken = $entity->shareToken;
        $expirationInterval = StringUtil::deserialize(Config::get('shareExpirationInterval'), true);
        $interval = 604800; // default: 7 days

        if (isset($expirationInterval['unit']) && isset($expirationInterval['value']) && $expirationInterval['value'] > 0) {
            $interval = System::getContainer()->get('huh.utils.date')->getTimePeriodInSeconds($expirationInterval);
        }

        return !$shareToken || !$entity->shareTokenTime || ($entity->shareTokenTime > $now + $interval);
    }

    public static function getFields(DataContainer $dc)
    {
        if (null === ($filterConfig = \Contao\System::getContainer()->get('huh.filter.registry')->findById($dc->activeRecord->filter))) {
            return [];
        }

        return \Contao\System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
            [
                'dataContainer' => $filterConfig->getFilter()['dataContainer'],
            ]
        );
    }

    public static function getTextFields(DataContainer $dc)
    {
        if (null === ($filterConfig = \Contao\System::getContainer()->get('huh.filter.registry')->findById($dc->activeRecord->filter))) {
            return [];
        }

        return \Contao\System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
            [
                'dataContainer' => $filterConfig->getFilter()['dataContainer'],
                'inputTypes' => ['text'],
            ]
        );
    }
}

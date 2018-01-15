<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\ListBundle\Util;

use Contao\Config;
use Contao\StringUtil;
use HeimrichHannot\UtilsBundle\Date\DateUtil;

class Helper
{
    public static function shareTokenExpiredOrEmpty($entity, $now)
    {
        $shareToken = $entity->shareToken;
        $expirationInterval = StringUtil::deserialize(Config::get('shareExpirationInterval'), true);
        $interval = 604800; // default: 7 days

        if (isset($expirationInterval['unit']) && isset($expirationInterval['value']) && $expirationInterval['value'] > 0) {
            $interval = DateUtil::getTimePeriodInSeconds($expirationInterval);
        }

        return !$shareToken || !$entity->shareTokenTime || ($entity->shareTokenTime > $now + $interval);
    }
}

<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Util;

use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\FieldValueCopierBundle\Util\ModelInstanceChoicePolyfill;
use HeimrichHannot\FilterBundle\Choice\FieldChoice;
use HeimrichHannot\FilterBundle\Util\AbstractChoice;
use HeimrichHannot\UtilsBundle\Util\Utils;

class ListConfigHelper
{
    public static function getFields(DataContainer $dc, ?AbstractChoice $choice = null, ?array $context = null)
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

        $_context = ['dataContainer' => $filter['dataContainer']];
        if (null !== $context) {
            $_context = array_merge($_context, $context);
        }

        $choice ??= System::getContainer()->get(FieldChoice::class);

        return $choice->getCachedChoices($_context);
    }

    public static function getTextFields(DataContainer $dc)
    {
        return static::getFields($dc, context: ['inputTypes' => ['text']]);
    }

    public static function getModelInstances(DataContainer $dc)
    {
        $choice = System::getContainer()->get();
        return static::getFields($dc, choice: $choice);
    }
}

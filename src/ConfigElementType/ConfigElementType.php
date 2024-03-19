<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ConfigElementType;

use Contao\CoreBundle\Framework\ContaoFramework;
use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;

/**
 * Interface ConfigElementType.
 *
 * @deprecated Use ListConfigElementTypeInterface instead
 */
interface ConfigElementType
{
    public function __construct(ContaoFramework $framework);

    public function addToItemData(ItemInterface $item, ListConfigElementModel $listConfigElement);
}
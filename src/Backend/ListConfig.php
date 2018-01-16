<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\ListBundle\Backend;

class ListConfig
{
    const SORTING_MODE_FIELD = 'field';
    const SORTING_MODE_TEXT = 'text';
    const SORTING_MODE_RANDOM = 'random';

    const SORTING_MODES = [
        self::SORTING_MODE_FIELD,
        self::SORTING_MODE_TEXT,
        self::SORTING_MODE_RANDOM,
    ];

    const SORTING_DIRECTION_ASC = 'asc';
    const SORTING_DIRECTION_DESC = 'desc';

    const SORTING_DIRECTIONS = [
        self::SORTING_DIRECTION_ASC,
        self::SORTING_DIRECTION_DESC,
    ];
}
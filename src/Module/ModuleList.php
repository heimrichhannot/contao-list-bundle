<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Module;

use Contao\Module;

/**
 * @deprecated
 */
class ModuleList extends Module
{
    /**
     * @deprecated Use ListFrontendModuleController::TYPE instead
     */
    const TYPE = 'huhlist';

    protected function compile()
    {
        // TODO: Implement compile() method.
    }
}

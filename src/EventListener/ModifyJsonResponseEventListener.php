<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\EventListener;

use Contao\System;
use HeimrichHannot\FilterBundle\Event\ModifyJsonResponseEvent;
use HeimrichHannot\ListBundle\Module\ModuleList;

class ModifyJsonResponseEventListener
{
    public function modifyResponse(ModifyJsonResponseEvent $event)
    {
        $response = $event->getResponse();
        $filter = $event->getFilter();
        $twig = System::getContainer()->get('twig');

        $module = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_module',
            $filter->getFilter()['ajaxList']);
        $list = new ModuleList($module);

        $data = json_decode($response->getContent(), true);

        $data['list'] = $list->generate();

        $response->setData($data);
    }
}

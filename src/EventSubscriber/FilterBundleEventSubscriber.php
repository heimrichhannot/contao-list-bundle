<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\EventSubscriber;

use Contao\Controller;
use HeimrichHannot\FilterBundle\Event\ModifyJsonResponseEvent;
use HeimrichHannot\ListBundle\Module\ModuleList;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FilterBundleEventSubscriber implements EventSubscriberInterface
{
    protected ModelUtil $modelUtil;

    public function __construct(ModelUtil $modelUtil)
    {
        $this->modelUtil = $modelUtil;
    }

    public static function getSubscribedEvents()
    {
        return [
            ModifyJsonResponseEvent::NAME => 'onModifyJsonResponseEvent',
        ];
    }

    public function onModifyJsonResponseEvent(ModifyJsonResponseEvent $event): void
    {
        $response = $event->getResponse();
        $filter = $event->getFilter();

        if (!$filter->getFilter()['ajaxList']) {
            return;
        }

        $module = $this->modelUtil->findModelInstanceByPk('tl_module', $filter->getFilter()['ajaxList']);
        $list = new ModuleList($module);

        $data = json_decode($response->getContent(), true);

        $data['list'] = Controller::replaceInsertTags($list->generate());

        $response->setData($data);
    }
}

<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\EventSubscriber;

use Contao\Controller;
use Contao\ModuleModel;
use HeimrichHannot\FilterBundle\Event\ModifyJsonResponseEvent;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FilterBundleEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var ModelUtil
     */
    protected $modelUtil;

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

        $module = ModuleModel::findByPk($filter->getFilter()['ajaxList']);

        if (!$module) {
            return;
        }

        $data = json_decode($response->getContent(), true);

        $data['list'] = Controller::replaceInsertTags(Controller::getFrontendModule($module->id));

        $response->setData($data);
    }
}

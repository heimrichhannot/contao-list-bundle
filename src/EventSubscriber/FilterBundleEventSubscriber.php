<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\EventSubscriber;

use Contao\Controller;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\ModuleModel;
use HeimrichHannot\FilterBundle\Event\ModifyJsonResponseEvent;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FilterBundleEventSubscriber implements EventSubscriberInterface
{
    protected Utils $utils;
    protected InsertTagParser $insertTagParser;

    public function __construct(
        Utils $utils,
        InsertTagParser $insertTagParser,
    ) {
        $this->utils = $utils;
        $this->insertTagParser = $insertTagParser;
    }

    public static function getSubscribedEvents(): array
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

        $data['list'] = $this->insertTagParser->replace(Controller::getFrontendModule($module->id));

        $response->setData($data);
    }
}
<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\EventListener;

use Contao\CoreBundle\Routing\UrlGenerator;
use HeimrichHannot\ListBundle\Event\ListBeforeRenderItemEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ListModalListener implements EventSubscriberInterface
{
    private UrlGenerator $urlGenerator;

    public function __construct(UrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            ListBeforeRenderItemEvent::NAME => 'onListBeforeRenderItemEvent',
        ];
    }

    public function onListBeforeRenderItemEvent(ListBeforeRenderItemEvent $event): void
    {
        $listConfigModel = $event->getItem()->getManager()->getListConfig();

        if (!$listConfigModel->openListItemsInModal) {
            return;
        }

        if ('listModalReaderType_huh_reader' !== $listConfigModel->listModalReaderType) {
            return;
        }

        $templateData = $event->getTemplateData();

        if (!isset($templateData['linkDataAttributes'])) {
            $templateData['linkDataAttributes'] = [];
        }

        $templateData['linkDataAttributes']['modalUrl'] = $this->urlGenerator->generate('huh_list_modal_reader', [
            'id' => $listConfigModel->id,
            'item' => $event->getItem()->getIdOrAlias(),
        ]);

        $event->setTemplateData($templateData);
    }
}

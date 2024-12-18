<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Event;

use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\ListBundle\ListConfiguration\ListConfiguration;
use Symfony\Contracts\EventDispatcher\Event;

class ListBeforeRenderItemEvent extends Event
{
    public const NAME = 'huh.list.event.item_before_render';

    protected string $templateName;

    protected array $templateData;

    protected ItemInterface   $item;

    private ListConfiguration $listConfiguration;

    public function __construct(
        string $templateName,
        array $templateData,
        ItemInterface $item,
        ListConfiguration $listConfiguration
    )
    {
        $this->templateName = $templateName;
        $this->templateData = $templateData;
        $this->item = $item;
        $this->listConfiguration = $listConfiguration;
    }

    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    public function setTemplateName(string $templateName): void
    {
        $this->templateName = $templateName;
    }

    public function getTemplateData(): array
    {
        return $this->templateData;
    }

    public function setTemplateData(array $templateData): void
    {
        $this->templateData = $templateData;
    }

    public function getItem(): ItemInterface
    {
        return $this->item;
    }

    public function setItem(ItemInterface $item): void
    {
        $this->item = $item;
    }

    public function getListConfiguration(): ListConfiguration
    {
        return $this->listConfiguration;
    }
}

<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Event;

use HeimrichHannot\ListBundle\Item\ItemInterface;
use Symfony\Component\EventDispatcher\Event;

class ListBeforeRenderItemEvent extends Event
{
    const NAME = 'huh.list.event.item_before_render';
    /**
     * @var string
     */
    protected $templateName;
    /**
     * @var array
     */
    protected $templateData;
    /**
     * @var ItemInterface
     */
    protected $item;

    /**
     * ListBeforeRenderItemEvent constructor.
     *
     * @param $templateName
     * @param array         $templateData
     * @param ItemInterface $item
     */
    public function __construct(string $templateName, array $templateData, ItemInterface $item)
    {
        $this->templateName = $templateName;
        $this->templateData = $templateData;
        $this->item = $item;
    }

    /**
     * @return string
     */
    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    /**
     * @param string $templateName
     */
    public function setTemplateName(string $templateName): void
    {
        $this->templateName = $templateName;
    }

    /**
     * @return array
     */
    public function getTemplateData(): array
    {
        return $this->templateData;
    }

    /**
     * @param array $templateData
     */
    public function setTemplateData(array $templateData): void
    {
        $this->templateData = $templateData;
    }

    /**
     * @return ItemInterface
     */
    public function getItem(): ItemInterface
    {
        return $this->item;
    }

    /**
     * @param ItemInterface $item
     */
    public function setItem(ItemInterface $item): void
    {
        $this->item = $item;
    }
}

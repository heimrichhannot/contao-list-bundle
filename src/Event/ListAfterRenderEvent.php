<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Event;

use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use Symfony\Component\EventDispatcher\Event;

class ListAfterRenderEvent extends Event
{
    const NAME = 'huh.list.event.list_after_render';

    /**
     * @var string
     */
    protected $rendered;

    /**
     * @var mixed
     */
    protected $templateData;

    /**
     * @var ListInterface
     */
    protected $list;

    /**
     * @var ListConfigModel
     */
    protected $listConfig;

    /**
     * @param string          $rendered
     * @param mixed           $templateData
     * @param ListInterface   $list
     * @param ListConfigModel $listConfig
     */
    public function __construct(string $rendered, $templateData, ListInterface $list, ListConfigModel $listConfig)
    {
        $this->rendered = $rendered;
        $this->templateData = $templateData;
        $this->list = $list;
        $this->listConfig = $listConfig;
    }

    /**
     * @return string
     */
    public function getRendered(): string
    {
        return $this->rendered;
    }

    /**
     * @param string $rendered
     */
    public function setRendered(string $rendered): void
    {
        $this->rendered = $rendered;
    }

    /**
     * @return mixed
     */
    public function getTemplateData()
    {
        return $this->templateData;
    }

    /**
     * @param mixed $templateData
     */
    public function setTemplateData($templateData): void
    {
        $this->templateData = $templateData;
    }

    /**
     * @return ListInterface
     */
    public function getList(): ListInterface
    {
        return $this->list;
    }

    /**
     * @param ListInterface $list
     */
    public function setList(ListInterface $list): void
    {
        $this->list = $list;
    }

    /**
     * @return ListConfigModel
     */
    public function getListConfig(): ListConfigModel
    {
        return $this->listConfig;
    }

    /**
     * @param ListConfigModel $listConfig
     */
    public function setListConfig(ListConfigModel $listConfig): void
    {
        $this->listConfig = $listConfig;
    }
}

<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Event;

use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use Symfony\Contracts\EventDispatcher\Event;

class ListBeforeRenderEvent extends Event
{
    const NAME = 'huh.list.event.list_before_render';

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
     * @param mixed $templateData
     */
    public function __construct(array $templateData, ListInterface $list, ListConfigModel $listConfig)
    {
        $this->templateData = $templateData;
        $this->list = $list;
        $this->listConfig = $listConfig;
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

    public function getList(): ListInterface
    {
        return $this->list;
    }

    public function setList(ListInterface $list): void
    {
        $this->list = $list;
    }

    public function getListConfig(): ListConfigModel
    {
        return $this->listConfig;
    }

    public function setListConfig(ListConfigModel $listConfig): void
    {
        $this->listConfig = $listConfig;
    }
}

<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Event;

use Contao\FrontendTemplate;
use Contao\Module;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use Symfony\Contracts\EventDispatcher\Event;

class ListCompileEvent extends Event
{
    const NAME = 'huh.list.event.list_compile';

    /**
     * @var FrontendTemplate
     */
    protected $template;

    /**
     * @var Module
     */
    protected $module;

    /**
     * @var ListConfigModel
     */
    protected $listConfig;

    public function __construct(FrontendTemplate $template, Module $module, ListConfigModel $listConfig)
    {
        $this->template = $template;
        $this->module = $module;
        $this->listConfig = $listConfig;
    }

    public function getTemplate(): FrontendTemplate
    {
        return $this->template;
    }

    public function setTemplate(FrontendTemplate $template): void
    {
        $this->template = $template;
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function setModule(Module $module): void
    {
        $this->module = $module;
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

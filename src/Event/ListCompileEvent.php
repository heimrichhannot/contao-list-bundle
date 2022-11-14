<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Event;

use Contao\FrontendTemplate;
use Contao\Module;
use Contao\ModuleModel;
use Contao\Template;
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
     * @var Module|ModuleModel
     */
    protected $module;

    /**
     * @var ListConfigModel
     */
    protected $listConfig;

    public function __construct(Template $template, $module, ListConfigModel $listConfig)
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

    /**
     * @return Module|ModuleModel
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @deprecated Unnecessary/ useless method
     */
    public function setModule($module): void
    {
        if ($module instanceof Module) {
            trigger_error('Usage of Module instances is deprecated!', \E_USER_WARNING);
        }

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

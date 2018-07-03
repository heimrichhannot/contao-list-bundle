<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\EventListener;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DC_Table;
use Contao\Input;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ListConfigElementCallbackListener
{
    const SELECTOR_FIELD = 'typeSelectorField';
    const TYPE_FIELD = 'typeField';

    /**
     * @var TranslatorInterface|TranslatorBagInterface
     */
    private $translator;
    /**
     * @var ModelUtil
     */
    private $modelUtil;
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    public function __construct(ContaoFrameworkInterface $framework, TranslatorInterface $translator, ModelUtil $modelUtil)
    {
        $this->translator = $translator;
        $this->modelUtil = $modelUtil;
        $this->framework = $framework;
    }

    /**
     * onload_callback.
     *
     * @param DC_Table $dc
     */
    public function updateLabel(DC_Table $dc)
    {
        /** @var Input $input */
        $input = $this->framework->getAdapter(Input::class);
        if (!$input->get('act') || 'edit' !== $input->get('act')) {
            return;
        }
        if (!$this->translator instanceof TranslatorBagInterface) {
            return;
        }
        $table = $dc->table;
        $configModel = $this->modelUtil->findModelInstanceByIdOrAlias($table, $dc->id);
        if (!$configModel) {
            return;
        }
        $type = $configModel->type;
        Controller::loadDataContainer($table);
        $dca = &$GLOBALS['TL_DCA'][$table];
        if (!strpos($dca['palettes'][$type], static::SELECTOR_FIELD)) {
            return;
        }

        if ($this->translator->getCatalogue()->has("huh.list.tl_list_config_element.field.typeSelectorField.$type.name") &&
            $this->translator->getCatalogue()->has("huh.list.tl_list_config_element.field.typeSelectorField.$type.desc")) {
            $dca['fields'][static::SELECTOR_FIELD]['label'] = [
                $this->translator->trans('huh.list.tl_list_config_element.field.typeSelectorField.'.$type.'.name'),
                $this->translator->trans('huh.list.tl_list_config_element.field.typeSelectorField.'.$type.'.desc'),
            ];
        }
        if (!strpos($dca['palettes'][$type], static::TYPE_FIELD)) {
            return;
        }
        if ($this->translator->getCatalogue()->has("huh.list.tl_list_config_element.field.typeField.$type.name") &&
            $this->translator->getCatalogue()->has("huh.list.tl_list_config_element.field.typeField.$type.desc")) {
            $dca['fields'][static::TYPE_FIELD]['label'] = [
                $this->translator->trans("huh.list.tl_list_config_element.field.typeField.$type.name"),
                $this->translator->trans("huh.list.tl_list_config_element.field.typeField.$type.desc"),
            ];
        }
    }
}

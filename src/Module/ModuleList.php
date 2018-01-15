<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\ListBundle\Module;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\ModuleModel;
use Contao\System;
use HeimrichHannot\ListBundle\Backend\ListBundle;
use HeimrichHannot\ListBundle\Backend\ListConfig;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\ListBundle\Util\Helper;
use HeimrichHannot\Request\Request;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use Patchwork\Utf8;

class ModuleList extends \Contao\Module
{
    protected $strTemplate = 'mod_list';

    /**
     * @var ContaoFramework
     */
    protected $framework;

    /**
     * @var ListConfigModel
     */
    protected $listConfig;

    public function __construct(ModuleModel $objModule, $strColumn = 'main')
    {
        $this->framework = System::getContainer()->get('contao.framework');

        parent::__construct($objModule, $strColumn);

        // add class to every list template
        $cssID = $this->cssID;
        $cssID[1] = $cssID[1].($cssID[1] ? ' ' : '').'huh-list';

        $this->cssID = $cssID;
    }

    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0]).' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    protected function compile()
    {
        Controller::loadDataContainer('tl_list_config');

        $this->listConfig = $listConfig = $this->getListConfig();
        $this->handleShare();

        // apply module fields to template
        $this->Template->headline = $this->headline;
        $this->Template->hl = $this->hl;

        // apply list config to template
        foreach ($listConfig->row() as $field => $value) {
            if (in_array($field, ['id', 'tstamp', 'dateAdded', 'title'], true)) {
                continue;
            }

            $this->Template->{$field} = $value;
        }

        $this->addDataAttributes();

//        $this->arrSkipInstances             = deserialize($this->skipInstances, true);
//        $this->arrTableFields               = deserialize($this->tableFields, true);
//        $this->addDefaultValues             = $this->formHybridAddDefaultValues;
//        $this->arrDefaultValues             = deserialize($this->formHybridDefaultValues, true);
//        $this->arrConjunctiveMultipleFields = deserialize($this->conjunctiveMultipleFields, true);

        // sorting
        $this->Template->currentSorting = $this->getCurrentSorting();

//        // set initial filters
//        $this->initInitialFilters();
//
//        // set default filter values
//        if ($this->addDefaultValues)
//        {
//            $this->applyDefaultFilters();
//        }

        if ($this->hasHeader) {
            $this->Template->header = $this->generateTableHeader();
        }

//        switch ($this->filterMode)
//        {
//            case OPTION_FORMHYBRID_FILTERMODE_MODULE:
//                if ($this->formHybridLinkedFilter)
//                {
//                    $this->linkedFilterModule = \ModuleModel::findByPk($this->formHybridLinkedFilter);
//
//                    if ($this->linkedFilterModule !== null)
//                    {
//                        $this->customFilterFields = $this->linkedFilterModule->customFilterFields;
//                        $this->objFilterForm      = new ListFilterForm($this);
//                    }
//                }
//                break;
//            default:
//                if (!$this->hideFilter)
//                {
//                    $this->objFilterForm        = new ListFilterForm($this);
//                    $this->Template->filterForm = $this->objFilterForm->generate();
//                }
//                break;
//        }

//        if ((!$this->hideFilter || ($this->filterMode == OPTION_FORMHYBRID_FILTERMODE_MODULE && $this->formHybridLinkedFilter))
//            && $this->objFilterForm->isSubmitted()
//            && !$this->objFilterForm->doNotSubmit()
//        )
//        {
//            // submission ain't formatted
//            list($objItems, $this->Template->count) = $this->getItems($this->objFilterForm->getSubmission(false));
//            $this->Template->isSubmitted = $this->objFilterForm->isSubmitted();
//        }
//        elseif ($this->showInitialResults)
//        {
//            list($objItems, $this->Template->count) = $this->getItems();
//        }
//
//        // Add the items
//        if ($objItems !== null)
//        {
//            $this->Template->items = $this->parseItems($objItems);
//        }
    }

    protected function generateTableHeader()
    {
        $headerFields = [];
        $currentSorting = $this->getCurrentSorting();
        $listConfig = $this->listConfig;

        foreach (\Contao\StringUtil::deserialize($listConfig->tableFields, true) as $name) {
            $isCurrentOrderField = ($name == $currentSorting['order']);

            $field = [
                'field' => $name,
            ];

            if ($isCurrentOrderField) {
                $field['class'] = (ListConfig::SORTING_DIRECTION_ASC == $currentSorting['sort'] ?
                    ListConfig::SORTING_DIRECTION_ASC : ListConfig::SORTING_DIRECTION_DESC);

                $field['link'] = UrlUtil::addQueryString(
                    'order='.$name.'&sort='.(ListConfig::SORTING_DIRECTION_ASC == $currentSorting['sort'] ?
                        ListConfig::SORTING_DIRECTION_DESC : ListConfig::SORTING_DIRECTION_ASC)
                );
            } else {
                $field['link'] = UrlUtil::addQueryString('order='.$name.'&sort='.ListConfig::SORTING_DIRECTION_ASC);
            }

            $headerFields[] = $field;
        }

        return $headerFields;
    }

    protected function addDataAttributes()
    {
        $dataAttributes = [];

        foreach ($GLOBALS['TL_DCA']['tl_list_config']['fields'] as $field => $data) {
            if ($data['addAsDataAttribute']) {
                $dataAttributes[StringUtil::camelCaseToDashed($field)] = $this->listConfig->{$field};
            }
        }

        $this->Template->dataAttributes = $dataAttributes;
    }

    protected function getListConfig()
    {
        $listConfigId = $this->arrData['listConfig'];

        if (!$listConfigId || null === ($listConfig = ModelUtil::getModelInstanceByPk($this->framework, 'tl_list_config', $listConfigId))) {
            throw new \Exception(sprintf('The module %s has no valid list config. Please set one.', $this->id));
        }

        return $listConfig;
    }

    protected function handleShare()
    {
        $listConfig = $this->listConfig;
        $action = Request::getGet('act');

        if (ListBundle::ACTION_SHARE == $action && $listConfig->addShare) {
            $url = Request::getGet('url');
            $id = Request::getGet('id');

            if (null !== ($entity = ModelUtil::getModelInstanceByPk($this->framework, $listConfig->dataContainer, $id))) {
                $now = time();

                if (Helper::shareTokenExpiredOrEmpty($entity, $now)) {
                    $shareToken = str_replace('.', '', uniqid('', true));
                    $entity->shareToken = $shareToken;
                    $entity->shareTokenTime = $now;
                    $entity->save();
                }

                if ($listConfig->shareAutoItem) {
                    $shareUrl = $url.'/'.$entity->shareToken;
                } else {
                    $shareUrl = UrlUtil::addQueryString('share='.$entity->shareToken, $url);
                }

                die($shareUrl);
            }
        }
    }

    protected function getCurrentSorting()
    {
        $listConfig = $this->listConfig;

        // GET parameter
        if (($orderField = Request::getGet('order')) && ($sort = Request::getGet('sort'))) {
            // anti sql injection: check if field exists
            if (Database::getInstance()->fieldExists($orderField, $listConfig->dataContainer) &&
                in_array($sort, ListConfig::SORTING_DIRECTIONS, true)) {
                $currentSorting = [
                    'order' => Request::getGet('order'),
                    'sort' => Request::getGet('sort'),
                ];
            } else {
                $currentSorting = [];
            }
        }
        // initial
        else {
            switch ($listConfig->sortingMode) {
                case ListConfig::SORTING_MODE_TEXT:
                    $currentSorting = [
                        'order' => $listConfig->sortingText,
                    ];
                    break;
                case ListConfig::SORTING_MODE_RANDOM:
                    $currentSorting = [
                        'order' => 'random',
                    ];
                    break;
                default:
                    $currentSorting = [
                        'order' => $listConfig->sortingField,
                        'sort' => $listConfig->sortingDirection,
                    ];
                    break;
            }
        }

        return $currentSorting;
    }
}

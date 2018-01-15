<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\ListBundle\Module;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\FrontendTemplate;
use Contao\ModuleModel;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\ListBundle\Backend\ListBundle;
use HeimrichHannot\ListBundle\Backend\ListConfig;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\ListBundle\Pagination\RandomPagination;
use HeimrichHannot\ListBundle\Util\Helper;
use HeimrichHannot\Request\Request;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use Patchwork\Utf8;

class ModuleList extends \Contao\Module
{
    protected $strTemplate = 'mod_list';

    /** @var ContaoFramework */
    protected $framework;

    /** @var ListConfigModel */
    protected $listConfig;

    /** @var FilterConfig */
    protected $filterConfig;

    /** @var object */
    protected $filter;

    /**
     * ModuleList constructor.
     *
     * @param ModuleModel $objModule
     * @param string      $strColumn
     */
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
        $this->filterConfig = $this->getFilterConfig();
        $this->filter = (object) $this->filterConfig->getFilter();
        $this->filterRegistry = System::getContainer()->get('huh.filter.registry');

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

        // sorting
        $this->Template->currentSorting = $this->getCurrentSorting();

        if ($this->hasHeader) {
            $this->Template->header = $this->generateTableHeader();
        }

        // apply filter
        $queryBuilder = $this->filterRegistry->getQueryBuilder($this->filter->id);
        $this->Template->isSubmitted = $this->filterConfig->getBuilder()->getForm()->isSubmitted();
        $this->Template->showResults = $this->Template->isSubmitted || $listConfig->showInitialResults;
        $this->Template->totalCount = $queryBuilder->select('*')->execute()->rowCount();

        $this->applyListConfigToQueryBuilder($queryBuilder);

        $items = $queryBuilder->execute()->fetchAll();

        echo '<pre>';
        var_dump($items);
        echo '</pre>';

//        if (!empty($items))
//        {
//            $this->Template->items = $this->prepareItems($items);
//        }
    }

    protected function applyListConfigToQueryBuilder(FilterQueryBuilder $queryBuilder)
    {
        $listConfig = $this->listConfig;

        // offset
        $offset = (int) ($listConfig->skipFirst);

        // limit
        $limit = null;

        if ($listConfig->numberOfItems > 0) {
            $limit = $listConfig->numberOfItems;
        }

        // total item number
        $totalCount = $this->Template->totalCount;

        // sorting
        $currentSorting = $this->getCurrentSorting();

        if (ListConfig::SORTING_MODE_RANDOM == $currentSorting['order']) {
            $randomSeed = Request::getGet(RandomPagination::PARAM_RANDOM) ?: rand(1, 500);
            $queryBuilder->orderBy('RAND("'.(int) $randomSeed.'")');
            list($offset, $limit) = $this->splitResults($offset, $totalCount, $limit, $randomSeed);
        } else {
            if (!empty($currentSorting)) {
                $queryBuilder->orderBy($currentSorting['order'], $currentSorting['sort']);
            }

            list($offset, $limit) = $this->splitResults($offset, $totalCount, $limit);
        }

        // split the results
        $queryBuilder->setFirstResult($offset)->setMaxResults($limit);
    }

    protected function splitResults($offset, $total, $limit, $randomSeed = null)
    {
        $listConfig = $this->listConfig;
        $offsettedTotal = $total - $offset;

        // Split the results
        if ($listConfig->perPage > 0 && (!isset($limit) || $listConfig->numberOfItems > $listConfig->perPage)) {
            // Adjust the overall limit
            if (isset($limit)) {
                $offsettedTotal = min($limit, $offsettedTotal);
            }

            // Get the current page
            $id = 'page_s'.$this->id;
            $page = Request::getGet($id) ?: 1;

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($offsettedTotal / $listConfig->perPage), 1)) {
                global $objPage;
                $objPage->noSearch = 1;
                $objPage->cache = 0;

                // Send a 404 header
                header('HTTP/1.1 404 Not Found');

                return;
            }

            // Set limit and offset
            $limit = $listConfig->perPage;
            $offset += (max($page, 1) - 1) * $listConfig->perPage;

            // Overall limit
            if ($offset + $limit > $offsettedTotal) {
                $limit = $offsettedTotal - $offset;
            }

            // Add the pagination menu
            if ($listConfig->addAjaxPagination) {
                $pagination = new RandomPagination(
                    $randomSeed, $offsettedTotal, $this->perPage, Config::get('maxPaginationLinks'), $id, new FrontendTemplate('pagination_ajax')
                );
            } else {
                $pagination = new RandomPagination(
                    $randomSeed, $offsettedTotal, $this->perPage, $GLOBALS['TL_CONFIG']['maxPaginationLinks'], $id
                );
            }

            $this->Template->pagination = $pagination->generate("\n  ");
        }

        return [$offset, $limit];
    }

    protected function prepareItems()
    {
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
                $field['class'] = (ListConfig::SORTING_DIRECTION_ASC
                                   == $currentSorting['sort'] ? ListConfig::SORTING_DIRECTION_ASC : ListConfig::SORTING_DIRECTION_DESC);

                $field['link'] = UrlUtil::addQueryString(
                    'order='.$name.'&sort='.(ListConfig::SORTING_DIRECTION_ASC
                                                   == $currentSorting['sort'] ? ListConfig::SORTING_DIRECTION_DESC : ListConfig::SORTING_DIRECTION_ASC)
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

        if (!$listConfigId || null === ($listConfig = System::getContainer()->get('huh.list.registry')->findByPk($listConfigId))) {
            throw new \Exception(sprintf('The module %s has no valid list config. Please set one.', $this->id));
        }

        return $listConfig;
    }

    protected function getFilterConfig()
    {
        $filterId = $this->listConfig->filter;

        if (!$filterId || null === ($filterConfig = System::getContainer()->get('huh.filter.registry')->findById($filterId))) {
            throw new \Exception(sprintf('The module %s has no valid filter. Please set one.', $this->id));
        }

        return $filterConfig;
    }

    protected function handleShare()
    {
        $listConfig = $this->listConfig;
        $action = Request::getGet('act');

        if (ListBundle::ACTION_SHARE == $action && $listConfig->addShare) {
            $url = Request::getGet('url');
            $id = Request::getGet('id');

            if (null !== ($entity = ModelUtil::findModelInstanceByPk($this->framework, $listConfig->dataContainer, $id))) {
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
            if (Database::getInstance()->fieldExists($orderField, $listConfig->dataContainer)
                && in_array($sort, ListConfig::SORTING_DIRECTIONS, true)
            ) {
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
                        'order' => ListConfig::SORTING_MODE_RANDOM,
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

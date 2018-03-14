<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Module;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Module;
use Contao\ModuleModel;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Manager\ListManagerInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use Patchwork\Utf8;

class ModuleList extends Module
{
    protected $strTemplate = 'mod_list';

    /**
     * @var ContaoFramework
     */
    protected $framework;

    /**
     * @var ListManagerInterface
     */
    protected $manager;

    /**
     * @var ListConfigModel
     */
    protected $listConfig;

    /**
     * @var FilterConfig
     */
    protected $filterConfig;

    /**
     * @var object
     */
    protected $filter;

    /**
     * @var Request
     */
    protected $request;

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

        Controller::loadDataContainer('tl_list_config');
        System::loadLanguageFile('tl_list_config');

        $this->manager = $this->getListManagerByName($this->getListConfig()->manager ?: 'default');
        $this->manager->setModuleData($this->arrData);
        $this->listConfig = $listConfig = $this->manager->getListConfig();

        $this->filterConfig = $this->manager->getFilterConfig();
        $this->filter = (object) $this->filterConfig->getFilter();
        $this->filterRegistry = System::getContainer()->get('huh.filter.registry');
        $this->request = System::getContainer()->get('huh.request');
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

        if (null === $this->manager) {
            return parent::generate();
        }

        $this->framework->getAdapter(Controller::class)->loadDataContainer('tl_list_config');
        $this->framework->getAdapter(Controller::class)->loadDataContainer($this->filter->dataContainer);
        $this->framework->getAdapter(System::class)->loadLanguageFile($this->filter->dataContainer);

        if (null !== ($listClass = $this->manager->getListByName($this->manager->getListConfig()->list ?: 'default'))) {
            $reflection = new \ReflectionClass($listClass);

            if (!$reflection->implementsInterface(ListInterface::class)) {
                throw new \Exception(sprintf('Item class %s must implement %s', $listClass, ListInterface::class));
            }

            if (!$reflection->implementsInterface(\JsonSerializable::class)) {
                throw new \Exception(sprintf('Item class %s must implement %s', $listClass, \JsonSerializable::class));
            }

            $this->manager->setList(new $listClass($this->manager));
        }

        return parent::generate();
    }

    public function getListConfig(): ListConfigModel
    {
        $listConfigId = $this->moduleData['listConfig'];

        if (!$listConfigId || null === ($listConfig = $this->listConfigRegistry->findByPk($listConfigId))) {
            throw new \Exception(sprintf('The module %s has no valid list config. Please set one.', $this->moduleData['id']));
        }

        // compute list config respecting the inheritance hierarchy
        $listConfig = $this->listConfigRegistry->computeListConfig(
            $listConfigId
        );

        return $listConfig;
    }

    protected function compile()
    {
        $this->manager->getList()->handleShare();

        // apply module fields to template
        $this->Template->headline = $this->headline;
        $this->Template->hl = $this->hl;

        // add class to every list template
        $cssID = $this->cssID;
        $cssID[1] = $cssID[1].($cssID[1] ? ' ' : '').'huh-list';

        $this->cssID = $cssID;

        $this->Template->list = function (string $listTemplate = null, string $itemTemplate = null, array $data = []) {
            return $this->manager->getList()->parse($listTemplate, $itemTemplate, $data);
        };
    }

//    protected function prepareItem(array $item): array
//    {
//        $listConfig = $this->listConfig;
//        $filter = $this->filter;
//        $formUtil = System::getContainer()->get('huh.utils.form');
//
//        $result = [];
//        $dca = &$GLOBALS['TL_DCA'][$filter->dataContainer];
//
//        $dc = DC_Table_Utils::createFromModelData($item, $filter->dataContainer);
//
//        $fields = $listConfig->limitFormattedFields ? StringUtil::deserialize($listConfig->formattedFields, true) : array_keys($dca['fields']);
//
//        if ($listConfig->isTableList) {
//            $result['tableFields'] = StringUtil::deserialize($listConfig->tableFields, true);
//        }
//
//        $result['raw'] = $item;
//
//        foreach ($fields as $field) {
//            $dc->field = $field;
//            $value = $item[$field];
//
//            if (is_array($dca['fields'][$field]['load_callback'])) {
//                foreach ($dca['fields'][$field]['load_callback'] as $callback) {
//                    $obj = System::importStatic($callback[0]);
//                    $value = $obj->{$callback[1]}($value, $dc);
//                }
//            }
//
//            $result['formatted'][$field] = $formUtil->prepareSpecialValueForOutput($field, $value, $dc);
//
//            // anti-xss: escape everything besides some tags
//            $result['formatted'][$field] = $formUtil->escapeAllHtmlEntities($filter->dataContainer, $field, $result['formatted'][$field]);
//        }
//
//        // add the missing field's raw values (these should always be inserted completely)
//        foreach (array_keys($dca['fields']) as $field) {
//            if (isset($result['raw'][$field])) {
//                continue;
//            }
//
//            $value = $item[$field];
//
//            if (is_array($dca['fields'][$field]['load_callback'])) {
//                foreach ($dca['fields'][$field]['load_callback'] as $callback) {
//                    $obj = System::importStatic($callback[0]);
//                    $value = $obj->{$callback[1]}($value, $dc);
//                }
//            }
//
//            // add raw value
//            $result['raw'][$field] = $value;
//        }
//
//        // HOOK: add custom logic
//        if (isset($GLOBALS['TL_HOOKS']['parseListItem']) && is_array($GLOBALS['TL_HOOKS']['parseListItem'])) {
//            foreach ($GLOBALS['TL_HOOKS']['parseListItem'] as $callback) {
//                $this->import($callback[0]);
//                $result = System::getContainer()->get($callback[0])->{$callback[1]}($result, $item, $this, $this->filterConfig, $this->listConfig);
//            }
//        }
//
//        return $result;
//    }

    /**
     * Get the list manager.
     *
     * @param string $name
     *
     * @throws \Exception
     *
     * @return null|ListManagerInterface
     */
    protected function getListManagerByName(string $name): ?ListManagerInterface
    {
        $config = System::getContainer()->getParameter('huh.list');

        if (!isset($config['list']['managers'])) {
            return null;
        }

        $managers = $config['list']['managers'];

        foreach ($managers as $manager) {
            if ($manager['name'] == $name) {
                if (!System::getContainer()->has($manager['id'])) {
                    return null;
                }

                /** @var ListManagerInterface $manager */
                $manager = System::getContainer()->get($manager['id']);
                $interfaces = class_implements($manager);

                if (!is_array($interfaces) || !in_array(ListManagerInterface::class, $interfaces, true)) {
                    throw new \Exception(sprintf('List manager service %s must implement %s', $manager['id'], ListManagerInterface::class));
                }

                return $manager;
            }
        }

        return null;
    }
}

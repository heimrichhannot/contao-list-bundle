<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Item;

use Contao\Controller;
use Contao\DataContainer;
use Contao\Environment;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ListBundle\Backend\ListBundle;
use HeimrichHannot\ListBundle\ConfigElementType\ConfigElementType;
use HeimrichHannot\ListBundle\Manager\ListManagerInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\Modal\ModalModel;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;

class DefaultItem implements ItemInterface, \JsonSerializable
{
    /**
     * Current Item Manager.
     *
     * @var ListManagerInterface
     */
    protected $_manager;

    /**
     * Current item data.
     *
     * @var array
     */
    protected $_raw = [];

    /**
     * Current formatted data.
     *
     * @var array
     */
    protected $_formatted = [];

    /**
     * @var string
     */
    protected $_cssClass;

    /**
     * @var int
     */
    protected $_count;

    /**
     * @var string
     */
    protected $_dataContainer;

    /**
     * @var string
     */
    protected $_idOrAlias;

    /**
     * @var bool
     */
    protected $_addDetails;

    /**
     * @var string
     */
    protected $_detailsUrl;

    /**
     * @var bool
     */
    protected $_addShare;

    /**
     * @var string
     */
    protected $_shareUrl;

    /**
     * @var bool
     */
    protected $_active;

    /**
     * @var bool
     */
    protected $_useModal;

    /**
     * @var int
     */
    protected $_jumpToDetails;

    /**
     * @var string
     */
    protected $_modalUrl;

    /**
     * @var array
     */
    protected $_tableFields;

    /**
     * @var DataContainer
     */
    protected $dc;

    /**
     * DefaultItem constructor.
     *
     * @param ListManagerInterface $_manager
     * @param array                $data     Raw item data
     */
    public function __construct(ListManagerInterface $_manager, array $data = [])
    {
        $this->_manager = $_manager;
        $this->setRaw($data);
    }

    /**
     * Magic getter.
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        if (isset($this->_raw[$name])) {
            return $this->_raw[$name];
        }

        return null;
    }

    /**
     * Magic setter.
     *
     * @param string $name
     * @param $value
     */
    public function __set(string $name, $value)
    {
        $dca = &$GLOBALS['TL_DCA'][$this->getDataContainer()];

        if (!$this->dc) {
            $this->dc = DC_Table_Utils::createFromModelData($this->getRaw(), $this->getDataContainer());
        }

        if (isset($dca['fields'][$name]['load_callback']) && is_array($dca['fields'][$name]['load_callback'])) {
            foreach ($dca['fields'][$name]['load_callback'] as $callback) {
                $this->dc->field = $name;

                $instance = System::importStatic($callback[0]);
                $value = $instance->{$callback[1]}($value, $this->dc);
            }
        }

        $this->_raw[$name] = $value;

        if (property_exists($this, $name)) {
            $this->{$name} = $value;
        }

        $this->setFormattedValue($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getRaw(): array
    {
        return $this->_raw;
    }

    /**
     * {@inheritdoc}
     */
    public function setRaw(array $data = []): void
    {
        $this->_raw = $data;
        $listConfig = $this->_manager->getListConfig();

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        if ($listConfig->isTableList) {
            $this->setTableFields(StringUtil::deserialize($listConfig->tableFields, true));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setFormattedValue(string $name, $value): void
    {
        $dca = &$GLOBALS['TL_DCA'][$this->getDataContainer()];

        if (!$this->dc) {
            $this->dc = DC_Table_Utils::createFromModelData($this->getRaw(), $this->getDataContainer());
        }

        $fields = $this->getManager()->getListConfig()->limitFormattedFields ? StringUtil::deserialize(
            $this->getManager()->getListConfig()->formattedFields,
            true
        ) : (isset($dca['fields']) && is_array($dca['fields']) ? array_keys($dca['fields']) : []);

        if (in_array($name, $fields, true)) {
            $this->dc->field = $name;

            $value = $this->_manager->getFormUtil()->prepareSpecialValueForOutput(
                $name,
                $value,
                $this->dc
            );

            // anti-xss: escape everything besides some tags
            $value = $this->_manager->getFormUtil()->escapeAllHtmlEntities(
                $this->getDataContainer(),
                $name,
                $value
            );

            // overwrite existing property with formatted value
            if (property_exists($this, $name)) {
                $this->{$name} = $value;
            }
        }

        $this->_formatted[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormattedValue(string $name)
    {
        return $this->_formatted[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatted(array $data = []): void
    {
        $this->_formatted = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatted(): array
    {
        $data = $this->_formatted;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawValue(string $name)
    {
        if (!isset($this->_raw[$name])) {
            return null;
        }

        return $this->_raw[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function setRawValue(string $name, $value): void
    {
        $this->_raw[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getManager(): ListManagerInterface
    {
        return $this->_manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataContainer(): string
    {
        $filter = (object) $this->_manager->getFilterConfig()->getFilter();

        return $filter->dataContainer;
    }

    /**
     * {@inheritdoc}
     */
    public function getModule(): array
    {
        return $this->_manager->getModuleData();
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $cssClass = '', int $count = 0): string
    {
        if (!$this->dc) {
            $this->dc = DC_Table_Utils::createFromModelData($this->getRaw(), $this->getDataContainer());
        }

        $listConfig = $this->_manager->getListConfig();
        $filter = (object) $this->_manager->getFilterConfig()->getFilter();

        // add list config element data
        if (null !== ($listConfigElements = $this->_manager->getListConfigElementRegistry()->findBy(['pid=?'], [$listConfig->id]))) {
            foreach ($listConfigElements as $listConfigElement) {
                if (null === ($class = $this->_manager->getListConfigElementRegistry()->getElementClassByName($listConfigElement->type))) {
                    continue;
                }

                /**
                 * @var ConfigElementType
                 */
                $type = $this->_manager->getFramework()->createInstance($class, [$this->_manager->getFramework()]);
                $type->addToItemData($this, $listConfigElement);
            }
        }

        $this->setCssClass($cssClass);
        $this->setCount($count);
        $this->setDataContainer($filter->dataContainer);

        // id or alias
        $idOrAlias = $this->generateIdOrAlias($this, $listConfig);

        $this->setIdOrAlias($idOrAlias);
        $this->setActive($idOrAlias && $this->_manager->getRequest()->getGet('items') == $idOrAlias);

        // details
        $this->addDetailsUrl($idOrAlias, $this, $listConfig);

        // share
        $this->addShareUrl($this, $listConfig);

        $twig = $this->_manager->getTwig();

        return $twig->render($this->_manager->getItemTemplateByName($listConfig->itemTemplate ?: 'default'), $this->jsonSerialize());
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return System::getContainer()->get('huh.utils.class')->jsonSerialize($this, $this->getFormatted());
    }

    public function generateIdOrAlias(ItemInterface $item, ListConfigModel $listConfig): string
    {
        $idOrAlias = $item->getRawValue('id');

        if ($listConfig->useAlias && ($alias = $item->getRawValue($listConfig->aliasField))) {
            $idOrAlias = $alias;
        }

        return $idOrAlias;
    }

    public function addDetailsUrl($idOrAlias, ItemInterface $item, ListConfigModel $listConfig): void
    {
        $this->setAddDetails($listConfig->addDetails);

        if ($listConfig->addDetails) {
            $this->setUseModal($listConfig->useModal);
            $this->setJumpToDetails($listConfig->jumpToDetails);

            $pageJumpTo = System::getContainer()->get('huh.utils.url')->getJumpToPageObject($listConfig->jumpToDetails);

            if (null !== $pageJumpTo) {
                if ($listConfig->useModal) {
                    if (null !== ($modal = ModalModel::findPublishedByTargetPage($pageJumpTo))) {
                        /** @var Controller $controller */
                        $controller = $this->_manager->getFramework()->getAdapter(Controller::class);

                        $this->setModalUrl($controller->replaceInsertTags(sprintf('{{modal_url::%s::%s::%s}}', $modal->id, $listConfig->jumpToDetails, $idOrAlias), true));
                    }
                } else {
                    $this->setDetailsUrl($pageJumpTo->getFrontendUrl('/'.$idOrAlias));
                }
            }
        }
    }

    public function addShareUrl(ItemInterface $item, ListConfigModel $listConfig): void
    {
        $this->setAddShare($listConfig->addShare);

        if ($listConfig->addShare) {
            $urlUtil = System::getContainer()->get('huh.utils.url');

            $pageJumpTo = $urlUtil->getJumpToPageObject($listConfig->jumpToShare);

            if (null !== $pageJumpTo) {
                $shareUrl = Environment::get('url').'/'.$pageJumpTo->getFrontendUrl();

                $url = $urlUtil->addQueryString('act='.ListBundle::ACTION_SHARE, $urlUtil->getCurrentUrl([
                    'skipParams' => true,
                ]));

                $url = $urlUtil->addQueryString('url='.urlencode($shareUrl), $url);

                if ($listConfig->useAlias && $item['raw'][$listConfig->aliasField]) {
                    $url = $urlUtil->addQueryString($listConfig->aliasField.'='.$item['raw'][$listConfig->aliasField], $url);
                } else {
                    $url = $urlUtil->addQueryString('id='.$item['raw']['id'], $url);
                }

                $this->setShareUrl($url);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCssClass(): ?string
    {
        return $this->_cssClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setCssClass(string $cssClass)
    {
        $this->_cssClass = $cssClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(): ?int
    {
        return $this->_count;
    }

    /**
     * {@inheritdoc}
     */
    public function setCount(int $count)
    {
        $this->_count = $count;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdOrAlias(): ?string
    {
        return $this->_idOrAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdOrAlias(string $idOrAlias)
    {
        $this->_idOrAlias = $idOrAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function getDetailsUrl(): ?string
    {
        return $this->_detailsUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function setDetailsUrl(string $detailsUrl)
    {
        $this->_detailsUrl = $detailsUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getShareUrl(): ?string
    {
        return $this->_shareUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function setShareUrl(string $shareUrl)
    {
        $this->_shareUrl = $shareUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function setDataContainer(string $dataContainer)
    {
        $this->_dataContainer = $dataContainer;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive(): ?bool
    {
        return $this->_active;
    }

    /**
     * {@inheritdoc}
     */
    public function setActive(bool $active)
    {
        $this->_active = $active;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAddDetails(): ?bool
    {
        return $this->_addDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function setAddDetails(bool $addDetails)
    {
        $this->_addDetails = $addDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAddShare(): ?bool
    {
        return $this->_addShare;
    }

    /**
     * {@inheritdoc}
     */
    public function setAddShare(bool $addShare)
    {
        $this->_addShare = $addShare;
    }

    /**
     * {@inheritdoc}
     */
    public function isUseModal(): ?bool
    {
        return $this->_useModal;
    }

    /**
     * {@inheritdoc}
     */
    public function setUseModal(bool $useModal)
    {
        $this->_useModal = $useModal;
    }

    /**
     * {@inheritdoc}
     */
    public function getJumpToDetails(): ?int
    {
        return $this->_jumpToDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function setJumpToDetails(int $jumpToDetails)
    {
        $this->_jumpToDetails = $jumpToDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function getModalUrl(): ?string
    {
        return $this->_modalUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function setModalUrl(string $modalUrl)
    {
        $this->_modalUrl = $modalUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getTableFields(): ?array
    {
        return $this->_tableFields;
    }

    /**
     * {@inheritdoc}
     */
    public function setTableFields(array $tableFields)
    {
        $this->_tableFields = $tableFields;
    }
}

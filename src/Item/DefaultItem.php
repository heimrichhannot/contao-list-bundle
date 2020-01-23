<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Item;

use Contao\Controller;
use Contao\DataContainer;
use Contao\Environment;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ListBundle\ConfigElementType\ConfigElementType;
use HeimrichHannot\ListBundle\ConfigElementType\ListConfigElementData;
use HeimrichHannot\ListBundle\Event\ListBeforeRenderItemEvent;
use HeimrichHannot\ListBundle\HeimrichHannotContaoListBundle;
use HeimrichHannot\ListBundle\Manager\ListManagerInterface;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
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
    protected $_jumpToDetailsMultilingual;

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
     * @var object|\Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher|\Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher
     */
    protected $_dispatcher;

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
        $this->_dispatcher = System::getContainer()->get('event_dispatcher');
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
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        if (property_exists($this, $name)) {
            return true;
        }

        if (isset($this->_raw[$name])) {
            return true;
        }

        return false;
    }

    /**
     * Magic setter.
     *
     * @param string $name
     * @param        $value
     */
    public function __set(string $name, $value)
    {
        $dca = &$GLOBALS['TL_DCA'][$this->getDataContainer()];

        if (!$this->dc) {
            $this->dc = DC_Table_Utils::createFromModelData($this->getRaw(), $this->getDataContainer());
        }

        if (isset($dca['fields'][$name]['load_callback']) && \is_array($dca['fields'][$name]['load_callback'])) {
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
        // do not format values in back end for performance reasons (sitemap…)
        if (System::getContainer()->get('huh.utils.container')->isBackend()) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA'][$this->getDataContainer()];

        if (!$this->dc) {
            $this->dc = DC_Table_Utils::createFromModelData($this->getRaw(), $this->getDataContainer());
        }

        $fields = $this->getManager()->getListConfig()->limitFormattedFields ? StringUtil::deserialize($this->getManager()->getListConfig()->formattedFields, true) : (isset($dca['fields']) && \is_array($dca['fields']) ? array_keys($dca['fields']) : []);

        if (\in_array($name, $fields)) {
            $this->dc->field = $name;

            $value = $this->_manager->getFormUtil()->prepareSpecialValueForOutput($name, $value, $this->dc);

            $value = Controller::replaceInsertTags($value);

            // anti-xss: escape everything besides some tags
            $value = $this->_manager->getFormUtil()->escapeAllHtmlEntities($this->getDataContainer(), $name, $value);

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
        /** @var ListConfigElementModel[] $listConfigElements */
        if (null !== ($listConfigElements = $this->_manager->getListConfigElementRegistry()->findBy(['pid=?'], [$listConfig->rootId]))) {
            foreach ($listConfigElements as $listConfigElement) {
                if ($listConfigElementType = $this->_manager->getListConfigElementRegistry()->getListConfigElementType($listConfigElement->type)) {
                    $listConfigElementType->addToListItemData(new ListConfigElementData($this, $listConfigElement));
                } else {
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

        $event = $this->_dispatcher->dispatch(ListBeforeRenderItemEvent::NAME, new ListBeforeRenderItemEvent($listConfig->itemTemplate, $this->jsonSerialize(), $this));
        $templateName = $this->_manager->getItemTemplateByName($event->getTemplateName() ?: 'default');

        return $twig->render($templateName, $event->getTemplateData());
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

    public function addDetailsUrl($idOrAlias, ItemInterface $item, ListConfigModel $listConfig, bool $absolute = false): void
    {
        $this->setAddDetails($listConfig->addDetails);

        if ($listConfig->addDetails) {
            $this->setUseModal($listConfig->useModal);
            $this->setJumpToDetails($listConfig->jumpToDetails);
            if($listConfig->jumpToDetailsMultilingual) {
                $this->setJumpToDetailsMultilingual($listConfig->jumpToDetailsMultilingual);
            }

            $jumpToDetails = $listConfig->jumpToDetails;
            $jumpToDetailsMultilingual = StringUtil::deserialize($listConfig->jumpToDetailsMultilingual, true);

            if (!empty($jumpToDetailsMultilingual)) {
                foreach ($jumpToDetailsMultilingual as $item) {
                    if (isset($item['language']) && $GLOBALS['TL_LANGUAGE'] === $item['language']) {
                        $jumpToDetails = $item['jumpTo'];

                        break;
                    }
                }
            }

            $pageJumpTo = System::getContainer()->get('huh.utils.url')->getJumpToPageObject($jumpToDetails);

            if (null !== $pageJumpTo) {
                if ($listConfig->useModal && isset(System::getContainer()->getParameter('kernel.bundles')['modal'])) {
                    if (null !== ($modal = \HeimrichHannot\Modal\ModalModel::findPublishedByTargetPage($pageJumpTo))) {
                        /** @var Controller $controller */
                        $controller = $this->_manager->getFramework()->getAdapter(Controller::class);

                        $this->setModalUrl($controller->replaceInsertTags(sprintf('{{modal_url::%s::%s::%s}}', $modal->id, $jumpToDetails, $idOrAlias), true));
                    }
                } else {
                    $this->setDetailsUrl(true === $absolute ? $pageJumpTo->getAbsoluteUrl('/'.$idOrAlias) : $pageJumpTo->getFrontendUrl('/'.$idOrAlias));
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

                $url = $urlUtil->addQueryString('act='.HeimrichHannotContaoListBundle::ACTION_SHARE, $urlUtil->getCurrentUrl([
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
    public function getDetailsUrl(bool $external = true): ?string
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
    public function setUseModal(?bool $useModal)
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
    public function getJumpToDetailsMultilingual(): ?string
    {
        return $this->_jumpToDetailsMultilingual;
    }

    /**
     * {@inheritdoc}
     */
    public function setJumpToDetailsMultilingual(?string $jumpToDetailsMultilingual)
    {
        $this->_jumpToDetailsMultilingual = $jumpToDetailsMultilingual;
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

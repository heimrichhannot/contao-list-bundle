<?php

namespace HeimrichHannot\ListBundle\Event;

use Contao\Model\Collection;
use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use Symfony\Component\EventDispatcher\Event;

class ListBeforeApplyConfigElementsEvent extends Event
{

    /**
     * @var Collection|null
     */
    private $listConfigElements;
    /**
     * @var ListConfigModel
     */
    private $listConfig;
    /**
     * @var ItemInterface
     */
    private $item;

    public function __construct(?Collection $listConfigElements, ListConfigModel $listConfig, ItemInterface $item)
    {

        $this->listConfigElements = $listConfigElements;
        $this->listConfig = $listConfig;
        $this->item = $item;
    }

    /**
     * @return ListConfigModel
     */
    public function getListConfig(): ListConfigModel
    {
        return $this->listConfig;
    }

    /**
     * @return ItemInterface
     */
    public function getItem(): ItemInterface
    {
        return $this->item;
    }

    /**
     * @return Collection|null
     */
    public function getListConfigElements(): ?Collection
    {
        return $this->listConfigElements;
    }

    /**
     * @param Collection|null $listConfigElements
     */
    public function setListConfigElements(?Collection $listConfigElements): void
    {
        $this->listConfigElements = $listConfigElements;
    }


}
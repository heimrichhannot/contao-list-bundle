<?php

namespace HeimrichHannot\ListBundle\Configuration;

use Contao\Model;
use HeimrichHannot\ListBundle\Model\ListConfigModel;

class ListConfiguration
{
    /** @var int */
    private $filter;

    /** @var bool */
    private $showInitialResults;

    /** @var ListConfigModel|array */
    private $source;

    /** @var Model|null */
    private $parent;

    /**
     * @return int
     */
    public function getFilter(): int
    {
        return $this->filter;
    }

    /**
     * @param int $filter
     * @return ListConfiguration
     */
    public function setFilter(int $filter): ListConfiguration
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @return bool
     */
    public function getShowInitialResults(): bool
    {
        return $this->showInitialResults;
    }

    /**
     * @param bool $showInitialResults
     * @return ListConfiguration
     */
    public function setShowInitialResults(bool $showInitialResults): ListConfiguration
    {
        $this->showInitialResults = $showInitialResults;
        return $this;
    }

    /**
     * Return the list config source. This may be the ListConfigModel or
     * the configuration array.
     *
     * @return array|ListConfigModel
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param array|ListConfigModel $source
     * @return ListConfiguration
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return Model|null
     */
    public function getParent(): ?Model
    {
        return $this->parent;
    }

    /**
     * @param Model|null $parent
     * @return ListConfiguration
     */
    public function setParent(?Model $parent): ListConfiguration
    {
        $this->parent = $parent;
        return $this;
    }
}
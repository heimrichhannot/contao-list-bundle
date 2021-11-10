<?php

namespace HeimrichHannot\ListBundle\Configuration;

class ListConfiguration
{
    /** @var int */
    private $filter;

    /** @var bool */
    private $showInitialResults;

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


}
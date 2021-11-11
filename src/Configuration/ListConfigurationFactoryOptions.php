<?php

namespace HeimrichHannot\ListBundle\Configuration;

class ListConfigurationFactoryOptions
{
    /** @var string|null */
    private $parentTable;

    /** @var int|null */
    private $parentId;

    /**
     * @return string|null
     */
    public function getParentTable(): ?string
    {
        return $this->parentTable;
    }

    /**
     * @param string $parentTable
     * @return ListConfigurationFactoryOptions
     */
    public function setParentTable(string $parentTable): ListConfigurationFactoryOptions
    {
        $this->parentTable = $parentTable;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     * @return ListConfigurationFactoryOptions
     */
    public function setParentId(int $parentId): ListConfigurationFactoryOptions
    {
        $this->parentId = $parentId;
        return $this;
    }


}
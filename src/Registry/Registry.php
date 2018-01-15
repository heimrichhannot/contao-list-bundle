<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\ListBundle\Registry;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class Registry
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Adapter function for the model's findBy method.
     *
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return \Contao\Model\Collection|ListConfigModel|null
     */
    public function findBy($column, $value, array $options = [])
    {
        return ModelUtil::findModelInstanceBy($this->framework, 'tl_list_config', $column, $value, $options);
    }

    /**
     * Adapter function for the model's findOneBy method.
     *
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return \Contao\Model\Collection|ListConfigModel|null
     */
    public function findOneBy($column, $value, array $options = [])
    {
        return ModelUtil::findModelInstanceBy($this->framework, 'tl_list_config', $column, $value, $options);
    }

    /**
     * Adapter function for the model's findByPk method.
     *
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return \Contao\Model\Collection|ListConfigModel|null
     */
    public function findByPk($pk, array $options = [])
    {
        return ModelUtil::findModelInstanceByPk($this->framework, 'tl_list_config', $pk, $options);
    }
}

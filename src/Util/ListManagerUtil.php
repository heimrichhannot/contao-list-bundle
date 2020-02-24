<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Util;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use HeimrichHannot\ListBundle\Exception\InvalidListManagerException;
use HeimrichHannot\ListBundle\Manager\ListManagerInterface;

class ListManagerUtil
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Get the list manager.
     *
     * @param string $name
     *
     * @throws InvalidListManagerException
     *
     * @return ListManagerInterface|null
     */
    public function getListManagerByName(string $name): ?ListManagerInterface
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

                if (!\is_array($interfaces) || !\in_array(ListManagerInterface::class, $interfaces)) {
                    throw new InvalidListManagerException(sprintf('List manager service %s must implement %s', $manager['id'], ListManagerInterface::class));
                }

                return $manager;
            }
        }

        return null;
    }
}

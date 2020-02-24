<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Exception;

use Throwable;

class InterfaceNotImplementedException extends \Exception
{
    /**
     * @var string
     */
    private $interface;
    /**
     * @var string
     */
    private $class;

    public function __construct(string $interface, string $class, string $message = '', int $code = 0, Throwable $previous = null)
    {
        if (empty($message)) {
            sprintf('List class %s must implement %s', $class, $interface);
        }
        parent::__construct($message, $code, $previous);
        $this->interface = $interface;
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getInterface(): string
    {
        return $this->interface;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }
}

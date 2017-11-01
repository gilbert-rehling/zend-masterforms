<?php

namespace Masterforms\Stdlib\Exception;

use Zend\Stdlib\Exception;

class InvalidArgumentException extends Exception\InvalidArgumentException implements
        ExceptionInterface
{
}
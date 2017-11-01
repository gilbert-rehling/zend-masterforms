<?php

namespace Masterforms\Stdlib\Exception;

use Masterforms\StdLib\Exception\ExceptionInterface;
use Zend\Db\Adapter\Exception;

class InvalidQueryException extends Exception\InvalidQueryException implements
        ExceptionInterface
{
}
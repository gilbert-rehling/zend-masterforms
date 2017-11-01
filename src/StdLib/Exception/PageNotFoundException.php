<?php

namespace Masterforms\Stdlib\Exception;

use Masterforms\Stdlib\Exception;

class PageNotFoundException extends Exception\FileNotFoundException
{

    /**
     * Exception code
     *
     * @var integer
     */
    protected $code = 404;

    /**
     * Exception error message
     *
     * @var string
     */
    protected $message = 'Page not found.';
}
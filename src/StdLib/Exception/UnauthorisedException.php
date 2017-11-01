<?php

namespace Masterforms\Stdlib\Exception;

/**
 * Exception when authentication is required and has failed or has not yet been provided.
 *
 * Savve\Stdlib\Exception
 *
 * @category Savve
 * @package Stdlib
 * @subpackage Exception
 */
class UnauthorisedException extends \RuntimeException
{

    /**
     * Exception error message
     *
     * @var string
     */
    protected $message = 'Unauthorised';

    /**
     * Exception code
     *
     * @var integer
     */
    protected $code = 401;

    /**
     * Constructor
     *
     * @param string|null $message [optional]
     * @param integer|null $code [optional]
     * @param \Exception|null $previous [optional]
     */
    public function __construct ($message = null, $code = null,\Exception $previous = null)
    {
        if (null !== $message) {
            $this->message = $message;
        }

        // set the exception code
        if (null !== $code) {
            $this->code = $code;
        }

        // set the appropriate header
        // header('WWW-Authenticate: Basic', true);

        parent::__construct($this->message, $this->code, $previous);
    }
}
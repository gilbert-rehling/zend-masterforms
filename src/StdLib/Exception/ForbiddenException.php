<?php

namespace Masterforms\Stdlib\Exception;

class ForbiddenException extends \RuntimeException
{

    /**
     * Exception error message
     *
     * @var string
     */
    protected $message = 'Unauthorised access.';

    /**
     * Exception code
     *
     * @var integer
     */
    protected $code = 403;

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

        parent::__construct($this->message, $this->code, $previous);
    }
}
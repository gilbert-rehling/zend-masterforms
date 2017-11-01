<?php

namespace Masterforms\Stdlib\Exception;

class FileNotFoundException extends \RuntimeException
{

    /**
     * Exception error message
     *
     * @var string
     */
    protected $message = 'File could not be found.';

    /**
     * Exception code
     *
     * @var integer
     */
    protected $code = 404;

    /**
     * File path
     *
     * @var string
     */
    private $path = null;

    /**
     * Constructor
     *
     * @param string|null $message [optional]
     * @param integer|null $code [optional]
     * @param \Exception|null $previous [optional]
     * @param string|null $path [optional]
     */
    public function __construct ($message = null, $code = null, \Exception $previous = null, $path = null)
    {
        if (null !== $message) {
            $this->message = $message;
        }

        // set the exception code
        if (null !== $code) {
            $this->code = $code;
        }

        // set the path of the file
        $this->path = $path;

        parent::__construct($this->message, $this->code, $previous);
    }
}
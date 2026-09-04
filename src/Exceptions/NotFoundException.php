<?php

namespace Exceptions;

class NotFoundException extends \Exception
{
    protected $message = 'Resource not found';
    protected $code = 404;

    public function __construct($message = null, $code = 404)
    {
        if ($message) {
            $this->message = $message;
        }
        parent::__construct($this->message, $code);
    }
}
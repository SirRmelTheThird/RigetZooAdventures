<?php

namespace Exceptions;

class PaymentException extends \Exception
{
    protected $message = 'Payment processing failed';
    protected $code = 402;

    public function __construct($message = null, $code = 402)
    {
        if ($message) {
            $this->message = $message;
        }
        parent::__construct($this->message, $code);
    }
}
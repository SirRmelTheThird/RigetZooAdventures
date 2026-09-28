<?php

declare(strict_types=1);

namespace Exceptions\Payment;

use Exceptions\System\UserFacingException;
use Core\Http\HttpStatus;

final class PaymentException extends UserFacingException
{
    public function __construct(string $message, ?string $redirectTo = null)
    {
        parent::__construct($message, HttpStatus::PaymentRequired, $redirectTo);
    }
}

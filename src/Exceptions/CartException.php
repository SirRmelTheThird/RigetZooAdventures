<?php

declare(strict_types=1);

namespace Exceptions;

use Core\HttpStatus;

final class CartException extends UserFacingException
{
    public function __construct(string $message, ?string $redirectTo = null)
    {
        parent::__construct($message, HttpStatus::BadRequest, $redirectTo);
    }
}

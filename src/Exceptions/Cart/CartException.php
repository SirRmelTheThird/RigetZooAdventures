<?php

declare(strict_types=1);

namespace Exceptions\Cart;

use Exceptions\System\UserFacingException;
use Core\Http\HttpStatus;

final class CartException extends UserFacingException
{
    public function __construct(string $message, ?string $redirectTo = null)
    {
        parent::__construct($message, HttpStatus::BadRequest, $redirectTo);
    }
}

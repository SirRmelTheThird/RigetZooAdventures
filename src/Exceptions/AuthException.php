<?php

declare(strict_types=1);

namespace Exceptions;

use Core\Constants\RedirectKey;
use Core\HttpStatus;
use Support\Messages;

final class AuthException extends UserFacingException
{
    public static function loginRequired(): self
    {
        return new self(Messages::LOGIN_REQUIRED, HttpStatus::Unauthorized, RedirectKey::LOGIN);
    }

    public static function invalidCredentials(): self
    {
        return new self(Messages::INVALID_CREDENTIALS, HttpStatus::Unauthorized, RedirectKey::LOGIN);
    }
}

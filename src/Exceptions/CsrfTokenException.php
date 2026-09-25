<?php

declare(strict_types=1);

namespace Exceptions;

use Core\HttpStatus;
use Support\Messages;

final class CsrfTokenException extends UserFacingException
{
    public function __construct()
    {
        parent::__construct(Messages::CSRF_INVALID, HttpStatus::Forbidden);
    }
}

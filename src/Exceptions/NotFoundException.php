<?php

declare(strict_types=1);

namespace Exceptions;

use Core\HttpStatus;

final class NotFoundException extends UserFacingException
{
    public function __construct(string $message)
    {
        parent::__construct($message, HttpStatus::NotFound);
    }
}

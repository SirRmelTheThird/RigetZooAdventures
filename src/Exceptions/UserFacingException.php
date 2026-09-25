<?php

declare(strict_types=1);

namespace Exceptions;

use Core\HttpStatus;
use RuntimeException;

abstract class UserFacingException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly HttpStatus $status,
        private readonly ?string $redirectTo = null,
    ) {
        parent::__construct($message, $status->value);
    }

    public function status(): HttpStatus
    {
        return $this->status;
    }

    public function redirectTo(): ?string
    {
        return $this->redirectTo;
    }
}

<?php

declare(strict_types=1);

namespace Exceptions;

use Core\HttpStatus;

final class ValidationException extends UserFacingException
{
    private const MESSAGE = 'Validation failed';

    public function __construct(private readonly array $errors)
    {
        parent::__construct(self::MESSAGE, HttpStatus::Unprocessable);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}

<?php

declare(strict_types=1);

namespace Exceptions\Validation;

use Exceptions\System\UserFacingException;
use Core\Http\HttpStatus;

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

<?php

declare(strict_types=1);

namespace Core\Validation;

use Exceptions\ValidationException;

final class ValidationResult
{
    public function __construct(private readonly array $errors)
    {
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function withError(string $field, string $message): self
    {
        return new self([...$this->errors, $field => $message]);
    }

    public function throwIfFailed(): void
    {
        if ($this->passes()) {
            return;
        }

        throw new ValidationException($this->errors);
    }
}

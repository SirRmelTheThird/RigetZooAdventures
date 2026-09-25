<?php

declare(strict_types=1);

namespace Core\Validation;

enum Rule: string
{
    case Required = 'required';
    case Email = 'email';
    case Integer = 'integer';
    case Min = 'min';
    case Max = 'max';
    case MinLength = 'minLength';
    case Date = 'date';
    case FutureDate = 'futureDate';

    public function requiresParameter(): bool
    {
        return match ($this) {
            self::Min, self::Max, self::MinLength => true,
            self::Required, self::Email, self::Integer, self::Date, self::FutureDate => false,
        };
    }

    public function message(): string
    {
        return match ($this) {
            self::Required => ':field is required.',
            self::Email => ':field must be a valid email address.',
            self::Integer => ':field must be an integer.',
            self::Min => ':field must be at least :param.',
            self::Max => ':field must not exceed :param.',
            self::MinLength => ':field must be at least :param characters.',
            self::Date => ':field must be a valid date.',
            self::FutureDate => ':field must not be in the past.',
        };
    }
}

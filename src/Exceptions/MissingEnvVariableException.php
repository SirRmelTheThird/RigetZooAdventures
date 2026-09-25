<?php

declare(strict_types=1);

namespace Exceptions;

use RuntimeException;

final class MissingEnvVariableException extends RuntimeException
{
    public function __construct(private readonly string $key)
    {
        parent::__construct(sprintf('Required environment variable "%s" is missing or empty.', $key));
    }

    public function key(): string
    {
        return $this->key;
    }

    public static function assert(string $key, mixed $value): void
    {
        if ($value === null) {
            throw new self($key);
        }

        if (!is_scalar($value)) {
            throw new self($key);
        }

        if (trim((string) $value) === '') {
            throw new self($key);
        }
    }
}

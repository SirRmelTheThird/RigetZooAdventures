<?php

declare(strict_types=1);

namespace Exceptions\System;

use RuntimeException;

final class InvalidEnvVariableException extends RuntimeException
{
    public function __construct(private readonly string $key, string $reason)
    {
        parent::__construct(sprintf('Environment variable "%s" is invalid: %s.', $key, $reason));
    }

    public function key(): string
    {
        return $this->key;
    }
}

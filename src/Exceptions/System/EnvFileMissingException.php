<?php

declare(strict_types=1);

namespace Exceptions\System;

use RuntimeException;

final class EnvFileMissingException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('.env file not found. Copy .env.example to .env and configure your settings.');
    }
}

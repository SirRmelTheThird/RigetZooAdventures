<?php

declare(strict_types=1);

namespace Core\Logging;

enum LogLevel: string
{
    case Debug = 'DEBUG';
    case Info = 'INFO';
    case Warning = 'WARNING';
    case Error = 'ERROR';

    public function priority(): int
    {
        return match ($this) {
            self::Debug => 0,
            self::Info => 1,
            self::Warning => 2,
            self::Error => 3,
        };
    }
}

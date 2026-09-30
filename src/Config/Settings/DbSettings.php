<?php

declare(strict_types=1);

namespace Config\Settings;

use Config\Config;

final class DbSettings
{
    public function __construct(
        public readonly string $host = 'localhost',
        public readonly int $port = 3306,
        public readonly string $database = 'rzet',
        public readonly string $username = 'root',
        public readonly string $password = '',
    ) {
    }

    public static function fromEnv(): self
    {
        return new self(
            host: Config::get('DB_HOST', 'localhost'),
            port: (int) Config::get('DB_PORT', 3306),
            database: Config::get('DB_DATABASE', 'rzet'),
            username: Config::get('DB_USERNAME', 'root'),
            password: Config::get('DB_PASSWORD', ''),
        );
    }
}

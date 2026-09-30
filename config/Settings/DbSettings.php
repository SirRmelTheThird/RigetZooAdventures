<?php

declare(strict_types=1);

namespace Config\Settings;

use Config\Config;
use Enums\EnvKey;

/**
 * Immutable database connection settings.
 */
final class DbSettings
{
    public function __construct(
        public readonly string $host,
        public readonly int $port,
        public readonly string $database,
        public readonly string $username,
        public readonly string $password,
    ) {
    }

    /**
     * Builds the settings from the environment.
     *
     * @throws \Exceptions\System\MissingEnvVariableException When a required value is empty.
     * @throws \Exceptions\System\InvalidEnvVariableException When DB_PORT is not an integer.
     */
    public static function fromEnv(): self
    {
        return new self(
            host: Config::require(EnvKey::DbHost),
            port: Config::requireInt(EnvKey::DbPort),
            database: Config::require(EnvKey::DbDatabase),
            username: Config::require(EnvKey::DbUsername),
            password: Config::requireAllowingEmpty(EnvKey::DbPassword),
        );
    }
}

<?php

declare(strict_types=1);

namespace Config;

use Config\Settings\DbSettings;
use Core\Exceptions\ConfigException;

final class Config
{
    /** @var array<string, string> */
    private static array $env = [];
    private static bool $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        $dotenvPath = dirname(__DIR__) . '/.env';

        if (!file_exists($dotenvPath)) {
            throw new ConfigException(
                sprintf('Configuration file %s not found. Copy .env.example to .env and configure your settings.', $dotenvPath)
            );
        }

        $dotenv = \Dotenv\Dotenv::createImmutable(dirname($dotenvPath));
        $dotenv->load();

        foreach ($_ENV as $key => $value) {
            self::$env[$key] = is_string($value) ? $value : (string) $value;
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$env[$key] ?? $default;
    }

    public static function isDebug(): bool
    {
        return filter_var((string) self::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    public static function shouldDisplayErrors(): bool
    {
        $debug = self::get('APP_DEBUG');

        if ($debug !== null) {
            return filter_var($debug, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        }

        $environment = strtolower((string) self::get('APP_ENV', 'production'));

        return in_array($environment, ['local', 'development', 'test'], true);
    }

    public static function dbSettings(): DbSettings
    {
        self::load();

        $host = self::get('DB_HOST');
        $port = self::get('DB_PORT', 3306);
        $database = self::get('DB_DATABASE');
        $username = self::get('DB_USERNAME');
        $password = self::get('DB_PASSWORD');

        if (empty($host)) {
            throw new ConfigException('Database host is required');
        }

        if (empty($database)) {
            throw new ConfigException('Database name is required');
        }

        if (empty($username)) {
            throw new ConfigException('Database username is required');
        }

        return new DbSettings(
            host: $host,
            port: (int) $port,
            database: $database,
            username: $username,
            password: $password,
        );
    }
}
<?php

declare(strict_types=1);

namespace Config;

use Dotenv\Dotenv;
use Exceptions\System\EnvFileMissingException;
use Exceptions\System\InvalidEnvVariableException;
use Exceptions\System\MissingEnvVariableException;
use Enums\EnvKey;

class Config
{
    private const ENV_FILE_NAME = '.env';

    /** @var array<string, string> */
    private static array $env = [];
    private static bool $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        $root = dirname(__DIR__);

        if (!file_exists($root . '/' . self::ENV_FILE_NAME)) {
            throw new EnvFileMissingException();
        }

        $dotenv = Dotenv::createImmutable($root);
        $dotenv->load();
        $dotenv->required(array_column(EnvKey::cases(), 'value'));

        foreach (EnvKey::cases() as $key) {
            self::$env[$key->value] = (string) $_ENV[$key->value];
        }

        self::$loaded = true;
    }

    /** Key must be present and non-empty. */
    public static function require(EnvKey $key): string
    {
        $value = self::lookup($key);

        MissingEnvVariableException::assert($key->value, $value);

        return $value;
    }

    /** Key must be present, but an empty value is valid (e.g. a local DB_PASSWORD). */
    public static function requireAllowingEmpty(EnvKey $key): string
    {
        return self::lookup($key);
    }

    /** Key must be present. An empty value means "not configured" and returns null. */
    public static function optional(EnvKey $key): ?string
    {
        $value = self::lookup($key);

        if (trim($value) === '') {
            return null;
        }

        return $value;
    }

    public static function shouldDisplayErrors(): bool
    {
        $debug = filter_var(
            self::require(EnvKey::AppDebug),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );

        if ($debug === null) {
            throw new InvalidEnvVariableException(EnvKey::AppDebug->value, 'must be a boolean');
        }

        return $debug;
    }

    private static function lookup(EnvKey $key): string
    {
        self::load();

        return self::$env[$key->value];
    }

    public static function requireInt(EnvKey $key): int
    {
        $value = filter_var(self::require($key), FILTER_VALIDATE_INT);

        if ($value === false) {
            throw new InvalidEnvVariableException($key->value, 'must be an integer');
        }

        return $value;
    }
}

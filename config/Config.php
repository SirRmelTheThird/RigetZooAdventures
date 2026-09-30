<?php

declare(strict_types=1);

namespace Config;

use Dotenv\Dotenv;
use Enums\EnvKey;
use Exceptions\System\EnvFileMissingException;
use Exceptions\System\InvalidEnvVariableException;
use Exceptions\System\MissingEnvVariableException;

/**
 * Typed, fail-fast access to environment configuration.
 *
 * The `.env` file is loaded once. Every {@see EnvKey} must be present at
 * load time, so accessors never need to re-check that a key exists.
 */
class Config
{
    private const ENV_FILE_NAME = '.env';

    /** @var array<string, string> */
    private static array $env = [];
    private static bool $loaded = false;

    /**
     * Loads and validates the `.env` file. Safe to call repeatedly.
     *
     * @throws EnvFileMissingException When the `.env` file does not exist.
     * @throws \Dotenv\Exception\ValidationException When a key from EnvKey is absent.
     */
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

    /**
     * Returns the value of a key that must be present and non-empty.
     *
     * @throws MissingEnvVariableException When the value is empty.
     */
    public static function require(EnvKey $key): string
    {
        $value = self::lookup($key);

        MissingEnvVariableException::assert($key->value, $value);

        return $value;
    }

    /**
     * Returns the value of a key that must be present but may be empty
     * (for example a local DB_PASSWORD).
     */
    public static function requireAllowingEmpty(EnvKey $key): string
    {
        return self::lookup($key);
    }

    /**
     * Returns the value, or null when the key is present but empty,
     * meaning "not configured".
     */
    public static function optional(EnvKey $key): ?string
    {
        $value = self::lookup($key);

        if (trim($value) === '') {
            return null;
        }

        return $value;
    }

    /**
     * Returns the value of a key that must be a non-empty integer.
     *
     * @throws MissingEnvVariableException When the value is empty.
     * @throws InvalidEnvVariableException When the value is not an integer.
     */
    public static function requireInt(EnvKey $key): int
    {
        $value = filter_var(self::require($key), FILTER_VALIDATE_INT);

        if ($value === false) {
            throw new InvalidEnvVariableException($key->value, 'must be an integer');
        }

        return $value;
    }

    /**
     * Whether errors should be shown to the user, driven by APP_DEBUG.
     *
     * @throws MissingEnvVariableException When APP_DEBUG is empty.
     * @throws InvalidEnvVariableException When APP_DEBUG is not a boolean.
     */
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

    /** Presence is guaranteed by load(), so no key check is needed here. */
    private static function lookup(EnvKey $key): string
    {
        self::load();

        return self::$env[$key->value];
    }
}
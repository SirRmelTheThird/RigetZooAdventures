<?php

namespace Config;

class Config
{
    private static $env = [];
    private static $loaded = false;

    public static function load()
    {
        if (self::$loaded) {
            return;
        }

        $envFile = dirname(__DIR__) . '/.env';

        if (!file_exists($envFile)) {
            throw new \Exception('.env file not found. Copy .env.example to .env and configure your settings.');
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes if present
                if (preg_match('/^"(.*)"$/', $value, $matches) || preg_match("/^'(.*)'$/", $value, $matches)) {
                    $value = $matches[1];
                }

                self::$env[$key] = $value;
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;

                // Also set as environment variable
                if (!getenv($key)) {
                    putenv("$key=$value");
                }
            }
        }

        self::$loaded = true;
    }

    public static function get($key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$env[$key] ?? $default;
    }

    public static function isDebug()
    {
        return self::get('APP_DEBUG', 'false') === 'true';
    }
}

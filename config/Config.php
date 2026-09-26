<?php
declare(strict_types=1);

namespace Config;

use Dotenv\Dotenv;

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

        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        foreach ($_ENV as $key => $value) {
            self::$env[$key] = is_string($value) ? $value : (string) $value;
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

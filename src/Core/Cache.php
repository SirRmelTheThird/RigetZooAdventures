<?php

namespace Core;

class Cache
{
    private static $store = [];
    private static $fileCachePath;

    private static function init()
    {
        if (!self::$fileCachePath) {
            self::$fileCachePath = dirname(__DIR__, 2) . '/storage/cache/';

            if (!is_dir(self::$fileCachePath)) {
                mkdir(self::$fileCachePath, 0755, true);
            }
        }
    }

    public static function remember($key, $ttl, $callback)
    {
        self::init();

        if (isset(self::$store[$key])) {
            $cached = self::$store[$key];
            if ($cached['expires'] > time()) {
                return $cached['value'];
            }
        }

        // Check file cache
        $cacheFile = self::$fileCachePath . md5($key) . '.cache';
        if (file_exists($cacheFile)) {
            $cached = unserialize(file_get_contents($cacheFile));
            if ($cached['expires'] > time()) {
                self::$store[$key] = $cached;
                return $cached['value'];
            }
        }

        $value = $callback();
        $cached = [
            'value' => $value,
            'expires' => time() + $ttl
        ];

        self::$store[$key] = $cached;
        file_put_contents($cacheFile, serialize($cached));

        return $value;
    }

    public static function put($key, $value, $ttl = 3600)
    {
        self::init();

        $cached = [
            'value' => $value,
            'expires' => time() + $ttl
        ];

        self::$store[$key] = $cached;

        $cacheFile = self::$fileCachePath . md5($key) . '.cache';
        file_put_contents($cacheFile, serialize($cached));
    }

    public static function get($key, $default = null)
    {
        self::init();

        if (isset(self::$store[$key])) {
            $cached = self::$store[$key];
            if ($cached['expires'] > time()) {
                return $cached['value'];
            }
        }

        // Check file cache
        $cacheFile = self::$fileCachePath . md5($key) . '.cache';
        if (file_exists($cacheFile)) {
            $cached = unserialize(file_get_contents($cacheFile));
            if ($cached['expires'] > time()) {
                self::$store[$key] = $cached;
                return $cached['value'];
            }
        }

        return $default;
    }

    public static function has($key)
    {
        return self::get($key) !== null;
    }

    public static function forget($key)
    {
        self::init();

        unset(self::$store[$key]);

        $cacheFile = self::$fileCachePath . md5($key) . '.cache';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    public static function clear()
    {
        self::init();

        self::$store = [];

        $files = glob(self::$fileCachePath . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }

        Logger::info('Cache cleared');
    }
}

<?php

namespace Core;

class Session
{
    private static $started = false;

    public static function start()
    {
        if (self::$started) {
            return;
        }

        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_strict_mode', 1);

        $cookieParams = [
            'lifetime' => 1800,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ];

        if (isset($_SERVER['HTTP_HOST'])) {
            $host = parse_url('http://' . $_SERVER['HTTP_HOST'], PHP_URL_HOST);
            if ($host && $host !== 'localhost' && !filter_var($host, FILTER_VALIDATE_IP)) {
                $cookieParams['domain'] = $host;
            }
        }

        session_set_cookie_params($cookieParams);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        self::$started = true;

        if (!isset($_SESSION['last_regeneration'])) {
            self::regenerate();
        } else {
            $interval = 60 * 30;
            if (time() - $_SESSION['last_regeneration'] >= $interval) {
                self::regenerate();
            }
        }
    }

    public static function regenerate()
    {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public static function remove($key)
    {
        unset($_SESSION[$key]);
    }

    public static function destroy()
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
        self::$started = false;
    }

    public static function flash($key, $value)
    {
        $_SESSION['flash'][$key] = $value;
    }

    public static function getFlash($key, $default = null)
    {
        $value = $_SESSION['flash'][$key] ?? $default;
        unset($_SESSION['flash'][$key]);
        return $value;
    }

    public static function isLoggedIn()
    {
        return isset($_SESSION['customer_id']) && isset($_SESSION['username']);
    }

    public static function getUserId()
    {
        return $_SESSION['customer_id'] ?? null;
    }

    public static function getUsername()
    {
        return $_SESSION['username'] ?? null;
    }
}

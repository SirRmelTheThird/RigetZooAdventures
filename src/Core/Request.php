<?php

namespace Core;

class Request
{
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public static function isPost()
    {
        return self::method() === 'POST';
    }

    public static function isGet()
    {
        return self::method() === 'GET';
    }

    public static function post($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }

        return $_POST[$key] ?? $default;
    }

    public static function get($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }

        return $_GET[$key] ?? $default;
    }

    public static function input($key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public static function all()
    {
        return array_merge($_GET, $_POST);
    }

    public static function uri()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        if (strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        return $uri;
    }

    public static function server($key, $default = null)
    {
        return $_SERVER[$key] ?? $default;
    }

    public static function isAjax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }

    public static function json($key = null, $default = null)
    {
        static $jsonData = null;
        if ($jsonData === null) {
            $input = file_get_contents('php://input');
            $jsonData = json_decode($input, true) ?? [];
        }

        if ($key === null) {
            return $jsonData;
        }

        return $jsonData[$key] ?? $default;
    }
}

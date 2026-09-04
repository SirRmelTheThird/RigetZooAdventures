<?php

namespace Core;

class Response
{
    public static function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }

    public static function back()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        self::redirect($referer);
    }

    public static function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function view($view, $data = [])
    {
        extract($data);

        $viewPath = dirname(__DIR__) . '/Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exceptions\NotFoundException("View not found: {$view}");
        }

        require $viewPath;
    }

    public static function setStatusCode($code)
    {
        http_response_code($code);
    }

    public static function notFound($message = 'Page not found')
    {
        self::setStatusCode(404);
        self::view('errors/404', ['message' => $message]);
        exit;
    }
}

<?php

namespace Core;

class Router
{
    private $routes = [];
    private $middlewares = [];

    public function get($uri, $controller, $middlewares = [])
    {
        $this->addRoute('GET', $uri, $controller, $middlewares);
        return $this;
    }

    public function post($uri, $controller, $middlewares = [])
    {
        $this->addRoute('POST', $uri, $controller, $middlewares);
        return $this;
    }

    private function addRoute($method, $uri, $controller, $middlewares = [])
    {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'controller' => $controller,
            'middlewares' => $middlewares
        ];
    }

    public function dispatch()
    {
        $requestMethod = Request::method();
        $requestUri = Request::uri();

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchUri($route['uri'], $requestUri)) {
                foreach ($route['middlewares'] as $middleware) {
                    $middlewareClass = "Middleware\\{$middleware}";
                    if (class_exists($middlewareClass)) {
                        $middlewareInstance = new $middlewareClass();
                        $middlewareInstance->handle();
                    }
                }

                $this->executeController($route['controller']);
                return;
            }
        }

        Response::notFound();
    }

    private function matchUri($pattern, $uri)
    {
        if ($pattern === $uri) {
            return true;
        }

        $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_-]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';

        return preg_match($pattern, $uri);
    }

    private function executeController($controller)
    {
        if (is_callable($controller)) {
            call_user_func($controller);
            return;
        }

        if (is_string($controller) && strpos($controller, '@') !== false) {
            list($class, $method) = explode('@', $controller);

            $controllerClass = "Controllers\\{$class}";

            if (!class_exists($controllerClass)) {
                throw new \Exceptions\NotFoundException("Controller not found: {$class}");
            }

            $controllerInstance = new $controllerClass();

            if (!method_exists($controllerInstance, $method)) {
                throw new \Exceptions\NotFoundException("Method not found: {$method}");
            }

            $controllerInstance->$method();
            return;
        }

        throw new \Exceptions\NotFoundException("Invalid controller handler");
    }
}

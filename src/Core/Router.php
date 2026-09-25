<?php

declare(strict_types=1);

namespace Core;

use Closure;
use Exceptions\NotFoundException;
use LogicException;
use Support\Messages;

final class Router
{
    private const CONTROLLER_NAMESPACE = 'Controllers\\';
    private const MIDDLEWARE_NAMESPACE = 'Middleware\\';
    private const HANDLER_PATTERN = '/^([A-Za-z0-9_]+)@([A-Za-z0-9_]+)$/';

    private array $routes = [];

    public function __construct(private readonly Closure $resolve)
    {
    }

    public function get(string $path, string $handler, array $middleware = []): self
    {
        return $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, string $handler, array $middleware = []): self
    {
        return $this->add('POST', $path, $handler, $middleware);
    }

    public function dispatch(Request $request): Response
    {
        $key = self::key($request->method(), $request->path());

        if (!array_key_exists($key, $this->routes)) {
            throw new NotFoundException(Messages::PAGE_NOT_FOUND);
        }

        $route = $this->routes[$key];

        foreach ($route->middleware as $name) {
            $response = ($this->resolve)(self::MIDDLEWARE_NAMESPACE . $name)->handle($request);

            if ($response !== null) {
                return $response;
            }
        }

        return $this->invoke($route, $request);
    }

    private function invoke(Route $route, Request $request): Response
    {
        $controller = ($this->resolve)($route->controllerClass);

        if (!method_exists($controller, $route->action)) {
            throw new LogicException("{$route->controllerClass} has no action {$route->action}()");
        }

        $response = $controller->{$route->action}($request);

        if (!$response instanceof Response) {
            throw new LogicException("{$route->controllerClass}::{$route->action}() must return a Core\\Response");
        }

        return $response;
    }

    private function add(string $method, string $path, string $handler, array $middleware): self
    {
        if (preg_match(self::HANDLER_PATTERN, $handler, $matches) !== 1) {
            throw new LogicException("Route handler must look like Controller@action, got '{$handler}'");
        }

        $key = self::key($method, $path);

        if (array_key_exists($key, $this->routes)) {
            throw new LogicException("Route already registered: {$key}");
        }

        $this->routes[$key] = new Route(self::CONTROLLER_NAMESPACE . $matches[1], $matches[2], $middleware);

        return $this;
    }

    private static function key(string $method, string $path): string
    {
        return $method . ' ' . $path;
    }
}

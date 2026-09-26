<?php

declare(strict_types=1);

namespace Core;

use Closure;
use Exceptions\NotFoundException;
use LogicException;
use Support\Messages;

final class Router
{
    private array $routes = [];

    public function __construct(
        private readonly RouteResolutionStrategy $routeResolution,
    ) {
    }

    public static function withResolver(Closure $resolve): self
    {
        return new self(new RouteResolutionStrategy($resolve));
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
            $response = $this->routeResolution->resolveMiddleware($name)->handle($request);

            if ($response !== null) {
                return $response;
            }
        }

        return $this->invoke($route, $request);
    }

    private function invoke(Route $route, Request $request): Response
    {
        $controller = $this->routeResolution->resolveController($route->controllerClass);

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
        [$controllerClass, $action] = $this->routeResolution->parseHandler($handler);

        $key = self::key($method, $path);

        if (array_key_exists($key, $this->routes)) {
            throw new LogicException("Route already registered: {$key}");
        }

        $this->routes[$key] = new Route($controllerClass, $action, $middleware);

        return $this;
    }

    private static function key(string $method, string $path): string
    {
        return $method . ' ' . $path;
    }
}

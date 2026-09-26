<?php

declare(strict_types=1);

namespace Core;

use Closure;
use LogicException;

final class RouteResolutionStrategy
{
    private const CONTROLLER_NAMESPACE = 'Controllers\\';
    private const MIDDLEWARE_NAMESPACE = 'Middleware\\';
    private const HANDLER_PATTERN = '/^([A-Za-z0-9_]+)@([A-Za-z0-9_]+)$/';

    public function __construct(private readonly Closure $resolve)
    {
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function parseHandler(string $handler): array
    {
        if (preg_match(self::HANDLER_PATTERN, $handler, $matches) !== 1) {
            throw new LogicException("Route handler must look like Controller@action, got '{$handler}'");
        }

        return [
            self::CONTROLLER_NAMESPACE . $matches[1],
            $matches[2],
        ];
    }

    public function resolveController(string $controllerClass): object
    {
        return ($this->resolve)($controllerClass);
    }

    public function resolveMiddleware(string $middlewareName): object
    {
        return ($this->resolve)(self::MIDDLEWARE_NAMESPACE . $middlewareName);
    }
}

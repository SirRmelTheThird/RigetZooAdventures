<?php

declare(strict_types=1);

namespace Core;

use Middleware\AuthMiddleware;
use Middleware\CSRFMiddleware;

final class MiddlewarePipeline
{
    /**
     * @param array<string>|Middleware[] $middleware
     */
    public function __construct(private array $middleware = [])
    {
    }

    public static function default(): self
    {
        return new self([
            AuthMiddleware::class,
            CSRFMiddleware::class,
        ]);
    }

    public function getOrdered(): array
    {
        return $this->middleware;
    }
}

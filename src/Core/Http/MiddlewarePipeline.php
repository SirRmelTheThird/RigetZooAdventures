<?php

declare(strict_types=1);

namespace Core\Http;

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

    /**
     * @param array<string>|null $middleware
     * @return array<string>
     */
    public function getOrdered(?array $middleware = null): array
    {
        if ($middleware === null) {
            return $this->middleware;
        }

        $priority = [];
        foreach ($this->middleware as $index => $registeredMiddleware) {
            $priority[$this->shortName($registeredMiddleware)] = $index;
        }

        $indexed = [];
        foreach ($middleware as $index => $middlewareName) {
            $indexed[] = [
                'name' => $middlewareName,
                'priority' => $priority[$this->shortName($middlewareName)] ?? PHP_INT_MAX,
                'index' => $index,
            ];
        }

        usort(
            $indexed,
            static fn (array $left, array $right): int =>
                ($left['priority'] <=> $right['priority']) ?: ($left['index'] <=> $right['index'])
        );

        return array_column($indexed, 'name');
    }

    private function shortName(string $middleware): string
    {
        $separator = strrpos($middleware, '\\');

        return $separator === false ? $middleware : substr($middleware, $separator + 1);
    }
}

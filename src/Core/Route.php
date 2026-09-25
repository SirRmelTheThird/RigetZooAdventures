<?php

declare(strict_types=1);

namespace Core;

final class Route
{
    public function __construct(
        public readonly string $controllerClass,
        public readonly string $action,
        public readonly array $middleware,
    ) {
    }
}

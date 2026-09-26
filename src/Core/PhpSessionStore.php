<?php

declare(strict_types=1);

namespace Core;

final class PhpSessionStore implements SessionStore
{
    public function has(string $key): bool
    {
        return Session::has($key);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Session::get($key, $default);
    }

    public function set(string $key, mixed $value): void
    {
        Session::set($key, $value);
    }

    public function remove(string $key): void
    {
        Session::remove($key);
    }
}

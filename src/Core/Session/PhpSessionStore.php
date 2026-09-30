<?php

declare(strict_types=1);

namespace Core\Session;

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

    public function flash(string $key, mixed $value): void
    {
        Session::flash($key, $value);
    }

    public function flashSuccess(string $message): void
    {
        Session::flashSuccess($message);
    }

    public function flashError(string $message): void
    {
        Session::flashError($message);
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        return Session::getFlash($key, $default);
    }

    public function isLoggedIn(): bool
    {
        return Session::isLoggedIn();
    }

    public function userId(): string
    {
        return (string) Session::userId();
    }

    public function getUserId(): ?string
    {
        return Session::getUserId();
    }

    public function getUsername(): ?string
    {
        return Session::getUsername();
    }

    public function signIn(string $customerId, string $username, string $firstName, string $email): void
    {
        Session::signIn($customerId, $username, $firstName, $email);
    }

    public function invalidate(): void
    {
        Session::invalidate();
    }

    public function regenerate(): void
    {
        Session::regenerate();
    }

    public function getCartCount(): int
    {
        return Session::getCartCount();
    }
}

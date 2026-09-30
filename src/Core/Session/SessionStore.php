<?php

declare(strict_types=1);

namespace Core\Session;

interface SessionStore
{
    public function has(string $key): bool;

    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value): void;

    public function remove(string $key): void;

    public function flash(string $key, mixed $value): void;

    public function flashSuccess(string $message): void;

    public function flashError(string $message): void;

    public function getFlash(string $key, mixed $default = null): mixed;

    public function isLoggedIn(): bool;

    public function userId(): string;

    public function getUserId(): ?string;

    public function getUsername(): ?string;

    public function signIn(string $customerId, string $username, string $firstName, string $email): void;

    public function invalidate(): void;

    public function regenerate(): void;

    public function getCartCount(): int;
}

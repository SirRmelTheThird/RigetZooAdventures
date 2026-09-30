<?php

declare(strict_types=1);

namespace Tests\Support;

use Core\Session\SessionStore;

final class InMemorySessionStore implements SessionStore
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(private array $data = [], private array $flashes = [])
    {
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->has($key)) {
            return $default;
        }

        return $this->data[$key];
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    public function flash(string $key, mixed $value): void
    {
        $this->flashes[$key] = $value;
    }

    public function flashSuccess(string $message): void
    {
        $this->flashes['success'][] = $message;
    }

    public function flashError(string $message): void
    {
        $this->flashes['error'][] = $message;
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        return $this->flashes[$key] ?? $default;
    }

    public function isLoggedIn(): bool
    {
        return isset($this->data['user_id']);
    }

    public function userId(): string
    {
        return (string) ($this->data['user_id'] ?? '');
    }

    public function getUserId(): ?string
    {
        return $this->data['user_id'] ?? null;
    }

    public function getUsername(): ?string
    {
        return $this->data['username'] ?? null;
    }

    public function signIn(string $customerId, string $username, string $firstName, string $email): void
    {
        $this->data['user_id'] = $customerId;
        $this->data['username'] = $username;
        $this->data['first_name'] = $firstName;
        $this->data['email'] = $email;
    }

    public function invalidate(): void
    {
        $this->data = [];
        $this->flashes = [];
    }

    public function regenerate(): void
    {
        // No-op for in-memory store
    }

    public function getCartCount(): int
    {
        return (int) ($this->data['cart_count'] ?? 0);
    }

    // Additional test helpers
    public function flashInfo(string $message): void
    {
        $this->flashes['info'][] = $message;
    }

    public function flashWarning(string $message): void
    {
        $this->flashes['warning'][] = $message;
    }

    public function getAllFlashes(): array
    {
        return $this->flashes;
    }

    public function clear(): void
    {
        $this->flashes = [];
    }

    public function hasFlashes(): bool
    {
        return !empty($this->flashes);
    }
}

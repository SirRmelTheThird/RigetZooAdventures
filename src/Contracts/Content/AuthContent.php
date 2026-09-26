<?php

declare(strict_types=1);

namespace App\Models;

class AuthContent implements AuthContentInterface
{
    public function __construct(private readonly array $data) {}

    public function getLogin(): array
    {
        return $this->data['login'] ?? [];
    }

    public function getSignup(): array
    {
        return $this->data['signup'] ?? [];
    }
}
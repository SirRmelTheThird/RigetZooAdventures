<?php

declare(strict_types=1);

namespace DTOs;

final class LoginCredentials
{
    public function __construct(
        public readonly string $username,
        public readonly string $password,
    ) {
    }
}

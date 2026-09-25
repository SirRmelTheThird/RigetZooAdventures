<?php

declare(strict_types=1);

namespace DTOs;

final class Registration
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $username,
        public readonly string $email,
        public readonly string $password,
    ) {
    }
}

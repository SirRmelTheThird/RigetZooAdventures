<?php

declare(strict_types=1);

namespace App\Models;

interface AuthContentInterface
{
    public function getLogin(): array;
    public function getSignup(): array;
}
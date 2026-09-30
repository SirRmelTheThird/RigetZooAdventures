<?php

declare(strict_types=1);

namespace Contracts;

interface AuthContentInterface
{
    public function getLogin(): array;
    public function getSignup(): array;
    public function getHeader(): array;
}

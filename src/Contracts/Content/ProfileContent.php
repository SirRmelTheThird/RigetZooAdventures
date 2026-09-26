<?php

declare(strict_types=1);

namespace App\Models;

class ProfileContent implements ProfileContentInterface
{
    public function __construct(private readonly array $data) {}

    public function getHeader(): array
    {
        return $this->data['header'] ?? [];
    }

    public function getOrders(): array
    {
        return $this->data['orders'] ?? [];
    }

    public function getAccount(): array
    {
        return $this->data['account'] ?? [];
    }

    public function getEmpty(): array
    {
        return $this->data['empty'] ?? [];
    }
}

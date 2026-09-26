<?php

declare(strict_types=1);

namespace App\Models;

class CheckoutContent implements CheckoutContentInterface
{
    public function __construct(private readonly array $data) {}

    public function getHeader(): array
    {
        return $this->data['header'] ?? [];
    }

    public function getPayment(): array
    {
        return $this->data['payment'] ?? [];
    }

    public function getSummary(): array
    {
        return $this->data['summary'] ?? [];
    }
}
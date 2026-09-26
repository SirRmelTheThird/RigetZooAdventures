<?php

declare(strict_types=1);

namespace App\Models;

class TicketsContent implements TicketsContentInterface
{
    public function __construct(private readonly array $data) {}

    public function getIndex(): array
    {
        return $this->data['index'] ?? [];
    }

    public function getTiers(): array
    {
        return $this->data['tiers'] ?? [];
    }

    public function getAges(): array
    {
        return $this->data['ages'] ?? [];
    }
}
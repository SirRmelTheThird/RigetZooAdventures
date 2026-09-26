<?php

declare(strict_types=1);

namespace App\Models;

class HomeContent implements HomeContentInterface
{
    public function __construct(private readonly array $data) {}

    public function getHero(): array
    {
        return $this->data['hero'] ?? [];
    }

    public function getFacts(): array
    {
        return $this->data['facts'] ?? [];
    }

    public function getVisit(): array
    {
        return $this->data['visit'] ?? [];
    }
}
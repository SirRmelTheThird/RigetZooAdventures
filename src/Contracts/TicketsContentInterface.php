<?php

declare(strict_types=1);

namespace App\Models;

interface TicketsContentInterface
{
    public function getIndex(): array;
    public function getTiers(): array;
    public function getAges(): array;
}

<?php

declare(strict_types=1);

namespace App\Models;

interface ProfileContentInterface
{
    public function getHeader(): array;
    public function getOrders(): array;
    public function getAccount(): array;
    public function getEmpty(): array;
}

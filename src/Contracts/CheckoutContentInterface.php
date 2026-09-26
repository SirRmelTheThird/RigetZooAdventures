<?php

declare(strict_types=1);

namespace App\Models;

interface CheckoutContentInterface
{
    public function getHeader(): array;
    public function getPayment(): array;
    public function getSummary(): array;
}

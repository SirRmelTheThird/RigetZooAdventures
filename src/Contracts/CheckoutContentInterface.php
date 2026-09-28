<?php

declare(strict_types=1);

namespace Contracts;

interface CheckoutContentInterface
{
    public function getHeader(): array;
    public function getPayment(): array;
    public function getSummary(): array;
}

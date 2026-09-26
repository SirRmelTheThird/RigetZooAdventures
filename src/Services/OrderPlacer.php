<?php

declare(strict_types=1);

namespace Services;

use Cart\Cart;

interface OrderPlacer
{
    public function placePaidOrder(string $customerId, Cart $cart, string $paymentIntentId): string;
}

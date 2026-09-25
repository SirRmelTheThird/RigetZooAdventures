<?php

declare(strict_types=1);

namespace Services;

use Cart\Cart;

interface OrderPlacer
{
    public function placePaidOrder(int $customerId, Cart $cart, string $paymentIntentId): int;
}

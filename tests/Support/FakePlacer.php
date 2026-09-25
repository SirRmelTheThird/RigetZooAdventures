<?php

declare(strict_types=1);

namespace Tests\Support;

use Cart\Cart;
use Services\OrderPlacer;
use Throwable;

final class FakePlacer implements OrderPlacer
{
    public int $calls = 0;
    public ?Throwable $failWith = null;

    public function placePaidOrder(int $customerId, Cart $cart, string $paymentIntentId): int
    {
        $this->calls++;

        if ($this->failWith !== null) {
            throw $this->failWith;
        }

        return 42;
    }
}

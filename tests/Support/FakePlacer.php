<?php

declare(strict_types=1);

namespace Tests\Support;

use Cart\Cart;
use Services\Orders\OrderPlacer;
use Throwable;

final class FakePlacer implements OrderPlacer
{
    public int $calls = 0;
    public ?Throwable $failWith = null;

    public function placePaidOrder(string $customerId, Cart $cart, string $paymentIntentId): string
    {
        $this->calls++;

        if ($this->failWith !== null) {
            throw $this->failWith;
        }

        return 'a2d92341-24c7-4589-94de-b525bd175cf32';
    }
}

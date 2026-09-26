<?php

declare(strict_types=1);

namespace Tests\Support;

use Cart\Cart;
use Cart\CartStore;

final class InMemoryCartStore implements CartStore
{
    private Cart $cart;

    public function __construct(?Cart $cart = null)
    {
        if ($cart === null) {
            $cart = Cart::empty();
        }

        $this->cart = $cart;
    }

    public function load(): Cart
    {
        return $this->cart;
    }

    public function save(Cart $cart): void
    {
        $this->cart = $cart;
    }
}

<?php

declare(strict_types=1);

namespace Cart;

interface CartStore
{
    public function load(): Cart;

    public function save(Cart $cart): void;
}

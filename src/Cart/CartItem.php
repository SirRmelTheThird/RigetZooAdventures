<?php

declare(strict_types=1);

namespace Cart;

use Enums\ItemType;

interface CartItem
{
    public function key(): string;

    public function type(): ItemType;

    public function total(): float;

    public function toArray(): array;
}

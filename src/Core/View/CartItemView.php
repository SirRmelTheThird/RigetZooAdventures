<?php

declare(strict_types=1);

namespace Core\View;

final readonly class CartItemView
{
    public function __construct(
        public string $title,
        public array $details,
        public float $total,
    ) {
    }
}

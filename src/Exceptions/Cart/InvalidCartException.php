<?php

declare(strict_types=1);

namespace Exceptions\Cart;

use RuntimeException;

final class InvalidCartException extends RuntimeException
{
    public static function missingItems(): self
    {
        return new self('Cart is missing its items list.');
    }

    public static function missingItemType(): self
    {
        return new self('Cart item is missing its type.');
    }
}
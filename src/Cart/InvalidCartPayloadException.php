<?php

declare(strict_types=1);

namespace Cart;

use RuntimeException;

final class InvalidCartPayloadException extends RuntimeException
{
    public static function unlessHasKeys(array $data, array $keys): void
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                throw new self("Cart payload is missing '{$key}'");
            }
        }
    }

    public static function unknownItemType(): self
    {
        return new self('Cart payload contains an unknown item type');
    }

    public static function malformedItems(): self
    {
        return new self('Cart payload items are malformed');
    }
}

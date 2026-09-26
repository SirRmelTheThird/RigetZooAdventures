<?php

declare(strict_types=1);

namespace Tests\Unit\Cart;

use Cart\SessionCartStore;
use Core\Logging\Logger;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemorySessionStore;
use Tests\Support\MemoryLogWriter;

final class SessionCartStoreTest extends TestCase
{
    public function testUnreadableSessionCartIsDiscardedNotFatal(): void
    {
        $session = new InMemorySessionStore([
            'cart' => ['items' => ['x' => ['type' => 'mystery']]],
        ]);
        $writer = new MemoryLogWriter();

        $store = new SessionCartStore($session, new Logger($writer));
        $cart = $store->load();

        self::assertTrue($cart->isEmpty());
        self::assertFalse($session->has('cart'));
        self::assertCount(1, $writer->lines);
    }
}
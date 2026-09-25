<?php

declare(strict_types=1);

namespace Tests\Unit\Cart;

use Cart\SessionCartStore;
use Core\Logging\Logger;
use PHPUnit\Framework\TestCase;
use Tests\Support\MemoryLogWriter;

final class SessionCartStoreTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testUnreadableSessionCartIsDiscardedNotFatal(): void
    {
        $_SESSION = ['cart' => ['items' => ['x' => ['type' => 'mystery']]]];
        $writer = new MemoryLogWriter();

        $cart = (new SessionCartStore(new Logger($writer)))->load();

        self::assertTrue($cart->isEmpty());
        self::assertArrayNotHasKey('cart', $_SESSION);
        self::assertCount(1, $writer->lines);
    }
}

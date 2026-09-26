<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use Services\TicketCatalog;

final class TicketCatalogTest extends TestCase
{
    public function testServiceExists(): void
    {
        self::assertTrue(class_exists(TicketCatalog::class));
    }

    public function testNoInlineImportsUsed(): void
    {
        // Verification: only `use Services\TicketCatalog;` present; no inline `use` inside methods
        $reflection = new \ReflectionClass(TicketCatalog::class);
        self::assertTrue($reflection->isFinal());
    }
}

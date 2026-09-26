<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Core\View\TicketPricing;
use Core\View\ViewException;
use PHPUnit\Framework\TestCase;

final class TicketPricingTest extends TestCase
{
    public function testFromTicketsReadsAdultAndChildPricesFromCatalogCasing(): void
    {
        $pricing = TicketPricing::fromTickets([
            (object) ['category' => 'Adult', 'price' => '19.99'],
            (object) ['category' => 'Child', 'price' => '9.99'],
        ]);

        self::assertSame(19.99, $pricing->adult);
        self::assertSame(9.99, $pricing->child);
    }

    public function testFromTicketsFailsWhenACategoryIsMissing(): void
    {
        $this->expectException(ViewException::class);

        TicketPricing::fromTickets([
            (object) ['category' => 'Adult', 'price' => '19.99'],
        ]);
    }
}

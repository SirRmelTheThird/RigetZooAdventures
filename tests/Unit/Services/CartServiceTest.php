<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Cart\Cart;
use Cart\CartStore;
use Core\Logging\Logger;
use DTOs\TicketSelection;
use Enums\TicketType;
use PHPUnit\Framework\TestCase;
use Repositories\EloquentCatalogRepository;
use Services\AccommodationService;
use Services\CartService;
use Services\TicketCatalog;
use Tests\Support\CartFixtures;
use Tests\Support\InMemoryCartStore;
use Tests\Support\MemoryLogWriter;

final class CartServiceTest extends TestCase
{
    use CartFixtures;

    public function testAddTicketsAddsTicketToCartAndLogs(): void
    {
        $store = new InMemoryCartStore();
        $service = $this->makeService($store);

        $service->addTickets(new TicketSelection(TicketType::Standard, 2, 1, $this->ticketDate()));

        self::assertFalse($store->load()->isEmpty());
    }

    public function testRemoveDeletesItemFromCart(): void
    {
        $ticket = $this->ticket();
        $store = new InMemoryCartStore(Cart::empty()->with($ticket));
        $service = $this->makeService($store);

        $service->remove($ticket->key());

        self::assertTrue($store->load()->isEmpty());
    }

    public function testClearEmptiesCart(): void
    {
        $store = new InMemoryCartStore(Cart::empty()->with($this->ticket(1, 0)));
        $service = $this->makeService($store);

        $service->clear();

        self::assertTrue($store->load()->isEmpty());
    }

    private function makeService(CartStore $store): CartService
    {
        $catalog = new TicketCatalog(new EloquentCatalogRepository());
        $accommodations = $this->createStub(AccommodationService::class);
        $logger = new Logger(new MemoryLogWriter());

        return new CartService($store, $catalog, $accommodations, $logger);
    }
}
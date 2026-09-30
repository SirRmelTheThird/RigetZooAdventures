<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Cart\Cart;
use Cart\CartStore;
use Core\Logging\Logger;
use DTOs\Tickets\TicketSelection;
use Enums\TicketType;
use PHPUnit\Framework\TestCase;
use Repositories\Eloquent\Catalog\EloquentCatalogRepository;
use Services\Accommodations\AccommodationService;
use Services\Checkout\CartService;
use Services\Tickets\TicketCatalog;
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
        $catalogRepo = $this->createStub(\Repositories\Contracts\Catalog\CatalogRepository::class);
        $adultTicket = new \Models\Tickets\Ticket();
        $adultTicket->price = '19.99';
        $childTicket = new \Models\Tickets\Ticket();
        $childTicket->price = '9.99';

        $catalogRepo
            ->method('priceFor')
            ->willReturnCallback(static function (\Enums\TicketType $type, \Enums\TicketCategory $category) use ($adultTicket, $childTicket) {
                return match ($category) {
                    \Enums\TicketCategory::Adult => $adultTicket,
                    \Enums\TicketCategory::Child => $childTicket,
                };
            });

        $catalog = new TicketCatalog($catalogRepo);

        $accommodationsRepo = $this->createStub(\Repositories\Contracts\Accommodation\AccommodationRepository::class);
        $accommodations = new AccommodationService($accommodationsRepo);
        $logger = new Logger(new MemoryLogWriter());

        return new CartService($store, $catalog, $accommodations, $logger);
    }
}

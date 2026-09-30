<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Enums\TicketCategory;
use Enums\TicketType;
use Exceptions\Http\NotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Models\Tickets\Ticket;
use PHPUnit\Framework\TestCase;
use Repositories\Contracts\Catalog\CatalogRepository;
use Services\Tickets\TicketCatalog;

final class TicketCatalogTest extends TestCase
{
    public function testPriceForReturnsTheCatalogPrice(): void
    {
        $ticket = new Ticket();
        $ticket->setRawAttributes(['price' => '19.99']);
        $catalog = new class ($ticket) implements CatalogRepository {
            public function __construct(private readonly Ticket $ticket)
            {
            }

            public function priceFor(TicketType $type, TicketCategory $category): ?Ticket
            {
                return $this->ticket;
            }

            public function forType(TicketType $type): Collection
            {
                return new Collection([$this->ticket]);
            }
        };

        self::assertSame(19.99, (new TicketCatalog($catalog))->priceFor(TicketType::Standard, TicketCategory::Adult));
    }

    public function testPriceForRaisesNotFoundWhenCatalogHasNoTicket(): void
    {
        $catalog = new class () implements CatalogRepository {
            public function priceFor(TicketType $type, TicketCategory $category): ?Ticket
            {
                return null;
            }

            public function forType(TicketType $type): Collection
            {
                return new Collection();
            }
        };

        $this->expectException(NotFoundException::class);
        (new TicketCatalog($catalog))->priceFor(TicketType::Standard, TicketCategory::Adult);
    }
}

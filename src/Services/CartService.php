<?php

declare(strict_types=1);

namespace Services;

use Cart\Cart;
use Cart\CartStore;
use Cart\TicketItem;
use Core\Logging\Logger;
use DTOs\AccommodationSelection;
use DTOs\TicketSelection;
use Enums\TicketCategory;

final class CartService
{
    public function __construct(
        private readonly CartStore $store,
        private readonly TicketCatalog $catalog,
        private readonly AccommodationService $accommodations,
        private readonly Logger $logger,
    ) {
    }

    public function cart(): Cart
    {
        return $this->store->load();
    }

    public function addTickets(TicketSelection $selection): void
    {
        $item = new TicketItem(
            $selection->ticketType,
            $selection->date,
            $selection->adult,
            $selection->child,
            $this->catalog->priceFor($selection->ticketType, TicketCategory::Adult),
            $this->catalog->priceFor($selection->ticketType, TicketCategory::Child),
        );

        $this->store->save($this->store->load()->with($item));

        $this->logger->info('Tickets added to cart', ['key' => $item->key(), 'total' => $item->total()]);
    }

    public function addAccommodation(AccommodationSelection $selection): void
    {
        $item = $this->accommodations->quote($selection);

        $this->store->save($this->store->load()->with($item));

        $this->logger->info('Accommodation added to cart', ['key' => $item->key(), 'total' => $item->total()]);
    }

    public function remove(string $key): void
    {
        $this->store->save($this->store->load()->without($key));
    }

    public function clear(): void
    {
        $this->store->save(Cart::empty());
    }
}

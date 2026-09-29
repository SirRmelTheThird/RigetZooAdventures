<?php

declare(strict_types=1);

namespace Services\Tickets;

use Enums\TicketCategory;
use Enums\TicketType;
use Models\Tickets\Ticket;
use Repositories\Contracts\Tickets\TicketRepository;

final class TicketInventory
{
    public function __construct(private readonly TicketRepository $tickets)
    {
    }

    public function reserve(TicketType $type, TicketCategory $category, int $quantity): Ticket
    {
        $ticket = $this->tickets->lockForReservation($type->value, $category);
        $this->tickets->decrementAvailableQuantity($ticket, $quantity);

        return $ticket;
    }
}

<?php

declare(strict_types=1);

namespace Services\Tickets;

use Enums\TicketCategory;
use Enums\TicketType;
use Exceptions\CartException;
use Exceptions\Http\NotFoundException;
use Models\Tickets\Ticket;
use Repositories\Contracts\Tickets\TicketRepository;
use Support\Messages;

final class TicketInventory
{
    public function __construct(private readonly TicketRepository $tickets)
    {
    }

    public function reserve(TicketType $type, TicketCategory $category, int $quantity): Ticket
    {
        return $this->tickets->reserve($type->value, $category, $quantity);
    }
}

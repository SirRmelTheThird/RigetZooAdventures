<?php

declare(strict_types=1);

namespace Services;

use Enums\TicketCategory;
use Enums\TicketType;
use Exceptions\CartException;
use Exceptions\NotFoundException;
use Models\Ticket;
use Repositories\Contracts\TicketRepository;
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

<?php

declare(strict_types=1);

namespace Services;

use Enums\TicketCategory;
use Enums\TicketType;
use Exceptions\CartException;
use Exceptions\NotFoundException;
use Models\Ticket;
use Support\Messages;

final class TicketInventory
{
    public function reserve(TicketType $type, TicketCategory $category, int $quantity): Ticket
    {
        $ticket = Ticket::ofType($type->value)->ofCategory($category->value)->lockForUpdate()->first();

        if ($ticket === null) {
            throw new NotFoundException(sprintf(Messages::TICKET_NOT_FOUND, $type->value, $category->value));
        }

        if (!$ticket->isAvailable($quantity)) {
            throw new CartException(sprintf(Messages::TICKETS_SOLD_OUT, $type->value, $category->value));
        }

        $ticket->decrement('available_quantity', $quantity);

        return $ticket;
    }
}

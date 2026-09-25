<?php

declare(strict_types=1);

namespace Services;

use Enums\TicketCategory;
use Enums\TicketType;
use Exceptions\NotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Models\Ticket;
use Support\Messages;

final class TicketCatalog
{
    public function priceFor(TicketType $type, TicketCategory $category): float
    {
        $ticket = Ticket::where('type', $type->value)
            ->where('category', $category->value)
            ->first();

        if ($ticket === null) {
            throw new NotFoundException(sprintf(Messages::TICKET_NOT_FOUND, $type->value, $category->value));
        }

        return (float) $ticket->price;
    }

    /** @return Collection<int, Ticket> */
    public function forType(TicketType $type): Collection
    {
        return Ticket::ofType($type->value)->orderBy('category')->get();
    }
}

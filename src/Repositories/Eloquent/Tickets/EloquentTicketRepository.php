<?php

declare(strict_types=1);

namespace Repositories\Eloquent\Tickets;

use Enums\TicketCategory;
use Illuminate\Database\Eloquent\Collection;
use Exceptions\NotFoundException;
use Models\Ticket;
use Support\Messages;
use Repositories\Contracts\TicketRepository;

final class EloquentTicketRepository implements TicketRepository
{
    public function findById(string $ticketId): ?Ticket
    {
        return Ticket::find($ticketId);
    }

    public function findByTicketTypeAndCategory(string $ticketType, TicketCategory $category): ?Ticket
    {
        return Ticket::ofType($ticketType)->ofCategory($category->value)->first();
    }

    public function reserve(string $ticketType, TicketCategory $category, int $quantity): Ticket
    {
        $ticket = Ticket::ofType($ticketType)->ofCategory($category->value)->lockForUpdate()->first();

        if ($ticket === null) {
            throw new NotFoundException(sprintf(Messages::TICKET_NOT_FOUND, $ticketType, $category->value));
        }

        $ticket->decrement('available_quantity', $quantity);

        return $ticket;
    }

    public function listAll(): Collection
    {
        return Ticket::all();
    }
}

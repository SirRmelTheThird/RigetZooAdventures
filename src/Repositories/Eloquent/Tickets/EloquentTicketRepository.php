<?php

declare(strict_types=1);

namespace Repositories\Eloquent\Tickets;

use Enums\TicketCategory;
use Illuminate\Database\Eloquent\Collection;
use Exceptions\Cart\CartException;
use Exceptions\Http\NotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Models\Tickets\Ticket;
use Support\Messages;
use Repositories\Contracts\Tickets\TicketRepository;

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

    public function lockForReservation(string $ticketType, TicketCategory $category): Ticket
    {
        $ticket = Ticket::ofType($ticketType)->ofCategory($category->value)->lockForUpdate()->first();

        if ($ticket === null) {
            throw new NotFoundException(sprintf(Messages::TICKET_NOT_FOUND, $ticketType, $category->value));
        }

        return $ticket;
    }

    public function decrementAvailableQuantity(Ticket $ticket, int $quantity): void
    {
        if (!$ticket->isAvailable($quantity)) {
            throw new CartException(sprintf(Messages::TICKETS_SOLD_OUT, $ticket->available_quantity, $ticket->category));
        }

        $ticket->decrement('available_quantity', $quantity);
    }

    public function listAll(): Collection
    {
        return Ticket::all();
    }
}

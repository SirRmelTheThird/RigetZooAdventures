<?php

declare(strict_types=1);

namespace Repositories\Contracts\Tickets;

use Enums\TicketCategory;
use Illuminate\Database\Eloquent\Collection;
use Models\Tickets\Ticket;

interface TicketRepository
{
    public function findById(string $ticketId): ?Ticket;
    public function findByTicketTypeAndCategory(string $ticketType, TicketCategory $category): ?Ticket;

    /**
     * Locks a ticket row for reservation.
     */
    public function lockForReservation(string $ticketType, TicketCategory $category): Ticket;

    /**
     * Applies the stock decrement after verifying availability.
     */
    public function decrementAvailableQuantity(Ticket $ticket, int $quantity): void;

    public function listAll(): Collection;
}

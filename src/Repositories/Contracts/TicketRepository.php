<?php

declare(strict_types=1);

namespace Repositories\Contracts;

use Enums\TicketCategory;
use Illuminate\Database\Eloquent\Collection;
use Models\Ticket;

interface TicketRepository
{
    public function findById(string $ticketId): ?Ticket;
    public function findByTicketTypeAndCategory(string $ticketType, TicketCategory $category): ?Ticket;
    public function reserve(string $ticketType, TicketCategory $category, int $quantity): Ticket;
    public function listAll(): Collection;
}

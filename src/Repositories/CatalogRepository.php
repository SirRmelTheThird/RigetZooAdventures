<?php

declare(strict_types=1);

namespace Repositories;

use Enums\TicketCategory;
use Enums\TicketType;
use Illuminate\Database\Eloquent\Collection;
use Models\Ticket;

interface CatalogRepository
{
    public function priceFor(TicketType $type, TicketCategory $category): ?Ticket;

    /** @return Collection<int, Ticket> */
    public function forType(TicketType $type): Collection;
}

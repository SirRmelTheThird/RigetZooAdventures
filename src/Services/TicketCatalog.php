<?php

declare(strict_types=1);

namespace Services;

use Enums\TicketCategory;
use Enums\TicketType;
use Exceptions\NotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Repositories\CatalogRepository;
use Support\Messages;

final class TicketCatalog
{
    public function __construct(private readonly CatalogRepository $catalog)
    {
    }

    public function priceFor(TicketType $type, TicketCategory $category): float
    {
        $ticket = $this->catalog->priceFor($type, $category);

        if ($ticket === null) {
            throw new NotFoundException(sprintf(Messages::TICKET_NOT_FOUND, $type->value, $category->value));
        }

        return (float) $ticket->price;
    }

    /** @return Collection<int, \Models\Ticket> */
    public function forType(TicketType $type): Collection
    {
        return $this->catalog->forType($type);
    }
}

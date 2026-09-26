<?php

declare(strict_types=1);

namespace Repositories\Eloquent\Catalog;

use Enums\TicketCategory;
use Enums\TicketType;
use Illuminate\Database\Eloquent\Collection;
use Models\Ticket;
use Repositories\Contracts\CatalogRepository;

final class EloquentCatalogRepository implements CatalogRepository
{
    public function priceFor(TicketType $type, TicketCategory $category): ?Ticket
    {
        return Ticket::where('type', $type->value)
            ->where('category', $category->value)
            ->first();
    }

    public function forType(TicketType $type): Collection
    {
        return Ticket::ofType($type->value)->orderBy('category')->get();
    }
}

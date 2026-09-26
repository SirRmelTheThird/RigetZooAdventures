<?php

declare(strict_types=1);

namespace Services;

use Enums\ItemType;
use Illuminate\Database\Eloquent\Collection;
use Models\OrderItem;

final class OrderItemLoader
{
    /**
     * @param Collection<int, OrderItem> $items
     */
    public function load(Collection $items): void
    {
        $ticketItems = $items->where(
            'item_type',
            ItemType::Ticket->value
        );

        $accommodationItems = $items->where(
            'item_type',
            ItemType::Accommodation->value
        );

        if ($ticketItems->isNotEmpty()) {
            $ticketItems->load(ItemType::Ticket->relationship());
        }

        if ($accommodationItems->isNotEmpty()) {
            $accommodationItems->load(ItemType::Accommodation->relationship());
        }
    }
}

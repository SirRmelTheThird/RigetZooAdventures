<?php

declare(strict_types=1);

namespace Repositories\Eloquent\Orders;

use Enums\ItemType;
use Models\OrderItem;
use Repositories\Contracts\OrderItemRepository;

final class EloquentOrderItemRepository implements OrderItemRepository
{
    public function createTicketLine(
        int $orderId,
        int $ticketId,
        int $quantity,
        float $price,
        string $startDate,
    ): void {
        OrderItem::create([
            'order_id' => $orderId,
            'item_type' => ItemType::Ticket->value,
            'ticket_id' => $ticketId,
            'quantity' => $quantity,
            'price' => $price,
            'start_date' => $startDate,
            'end_date' => null,
        ]);
    }

    public function createAccommodationLine(
        int $orderId,
        int $accommodationId,
        float $price,
        string $startDate,
        string $endDate,
    ): void {
        OrderItem::create([
            'order_id' => $orderId,
            'item_type' => ItemType::Accommodation->value,
            'accommodation_id' => $accommodationId,
            'quantity' => 1,
            'price' => $price,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }
}

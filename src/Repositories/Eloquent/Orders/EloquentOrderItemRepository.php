<?php

declare(strict_types=1);

namespace Repositories\Eloquent\Orders;

use Enums\ItemType;
use Models\Orders\OrderItem;
use Repositories\Contracts\Orders\OrderItemRepository;

final class EloquentOrderItemRepository implements OrderItemRepository
{
    public function createTicketLine(
        string $orderId,
        string $ticketId,
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
        string $orderId,
        string $accommodationId,
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

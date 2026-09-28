<?php

declare(strict_types=1);

namespace Repositories\Contracts\Orders;

interface OrderItemRepository
{
    public function createTicketLine(
        string $orderId,
        string $ticketId,
        int $quantity,
        float $price,
        string $startDate,
    ): void;

    public function createAccommodationLine(
        string $orderId,
        string $accommodationId,
        float $price,
        string $startDate,
        string $endDate,
    ): void;
}

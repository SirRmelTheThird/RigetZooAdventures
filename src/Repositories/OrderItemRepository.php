<?php

declare(strict_types=1);

namespace Repositories;

interface OrderItemRepository
{
    public function createTicketLine(
        int $orderId,
        int $ticketId,
        int $quantity,
        float $price,
        string $startDate,
    ): void;

    public function createAccommodationLine(
        int $orderId,
        int $accommodationId,
        float $price,
        string $startDate,
        string $endDate,
    ): void;
}

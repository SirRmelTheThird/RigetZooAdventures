<?php

declare(strict_types=1);

namespace DTOs\Tickets;

use Enums\TicketType;

final class TicketSelection
{
    public function __construct(
        public readonly TicketType $ticketType,
        public readonly int $adult,
        public readonly int $child,
        public readonly string $date,
    ) {
    }
}

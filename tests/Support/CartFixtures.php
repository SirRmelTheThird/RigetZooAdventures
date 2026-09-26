<?php

declare(strict_types=1);

namespace Tests\Support;

use Cart\AccommodationItem;
use Cart\Cart;
use Cart\TicketItem;
use Enums\TicketType;

/**
 * Shared cart builders used across Cart and Checkout tests.
 */
trait CartFixtures
{
    private function ticketCart(int $adult = 2, int $child = 1): Cart
    {
        return Cart::empty()->with($this->ticket($adult, $child));
    }

    private function ticket(int $adult = 2, int $child = 1): TicketItem
    {
        return new TicketItem(
            TicketType::Standard,
            $this->ticketDate(),
            $adult,
            $child,
            $this->ticketAdultPrice(),
            $this->ticketChildPrice(),
        );
    }

    private function stay(int $id = 7): AccommodationItem
    {
        return new AccommodationItem($id, 'Lodge', '2030-02-01', '2030-02-04', 3, 120.0, 2);
    }

    private function ticketDate(): string
    {
        return '2030-01-15';
    }

    private function ticketAdultPrice(): float
    {
        return 19.99;
    }

    private function ticketChildPrice(): float
    {
        return 9.99;
    }

    private function ticketKey(): string
    {
        return sprintf('ticket_%s_%s', TicketType::Standard->value, $this->ticketDate());
    }

    private function accommodationKey(int $id = 7): string
    {
        return sprintf('accommodation_%d', $id);
    }
}
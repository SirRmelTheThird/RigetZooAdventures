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
        return Cart::empty()->with(new TicketItem(TicketType::Standard, '2030-01-15', $adult, $child, 19.99, 9.99));
    }

    private function stay(int $id = 7): AccommodationItem
    {
        return new AccommodationItem($id, 'Lodge', '2030-02-01', '2030-02-04', 3, 120.0, 2);
    }
}

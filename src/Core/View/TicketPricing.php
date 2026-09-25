<?php

declare(strict_types=1);

namespace Core\View;

final readonly class TicketPricing
{
    private function __construct(
        public float $adult,
        public float $child,
    ) {
    }

    public static function fromTickets(iterable $tickets): self
    {
        $prices = [];
        foreach ($tickets as $ticket) {
            $category = TicketCategory::tryFrom((string) $ticket->category);
            if ($category === null) {
                continue;
            }
            $prices[$category->value] = (float) $ticket->price;
        }

        foreach (TicketCategory::cases() as $category) {
            if (!array_key_exists($category->value, $prices)) {
                throw ViewException::missingTicketPrice($category);
            }
        }

        return new self(
            $prices[TicketCategory::Adult->value],
            $prices[TicketCategory::Child->value],
        );
    }
}

<?php

declare(strict_types=1);

namespace Cart;

use Enums\ItemType;
use Enums\TicketType;

final class TicketItem implements CartItem
{
    private const REQUIRED_KEYS = ['ticketType', 'adult', 'adultPrice', 'child', 'childPrice', 'date'];

    public function __construct(
        public readonly TicketType $ticketType,
        public readonly string $date,
        public readonly int $adult,
        public readonly int $child,
        public readonly float $adultPrice,
        public readonly float $childPrice,
    ) {
    }

    public static function fromArray(array $data): self
    {
        InvalidCartPayloadException::unlessHasKeys($data, self::REQUIRED_KEYS);

        return new self(
            TicketType::from((string) $data['ticketType']),
            (string) $data['date'],
            (int) $data['adult'],
            (int) $data['child'],
            (float) $data['adultPrice'],
            (float) $data['childPrice'],
        );
    }

    public function key(): string
    {
        return "ticket_{$this->ticketType->value}_{$this->date}";
    }

    public function type(): ItemType
    {
        return ItemType::Ticket;
    }

    public function total(): float
    {
        return round($this->adult * $this->adultPrice + $this->child * $this->childPrice, 2);
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type()->cartType(),
            'ticketType' => $this->ticketType->value,
            'adult' => $this->adult,
            'adultPrice' => $this->adultPrice,
            'child' => $this->child,
            'childPrice' => $this->childPrice,
            'date' => $this->date,
            'total' => $this->total(),
        ];
    }
}

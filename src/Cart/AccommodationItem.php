<?php

declare(strict_types=1);

namespace Cart;

use Enums\ItemType;

final class AccommodationItem implements CartItem
{
    private const REQUIRED_KEYS = ['id', 'name', 'startDate', 'endDate', 'nights', 'pricePerNight', 'guests'];

    public function __construct(
        public readonly string $accommodationId,
        public readonly string $name,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly int $nights,
        public readonly float $pricePerNight,
        public readonly int $guests,
    ) {
    }

    public static function fromArray(array $data): self
    {
        InvalidCartPayloadException::unlessHasKeys($data, self::REQUIRED_KEYS);

        return new self(
            (string) $data['id'],
            (string) $data['name'],
            (string) $data['startDate'],
            (string) $data['endDate'],
            (int) $data['nights'],
            (float) $data['pricePerNight'],
            (int) $data['guests'],
        );
    }

    public function key(): string
    {
        return "accommodation_{$this->accommodationId}";
    }

    public function type(): ItemType
    {
        return ItemType::Accommodation;
    }

    public function total(): float
    {
        return round($this->nights * $this->pricePerNight, 2);
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type()->cartType(),
            'id' => $this->accommodationId,
            'name' => $this->name,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'nights' => $this->nights,
            'pricePerNight' => $this->pricePerNight,
            'guests' => $this->guests,
            'total' => $this->total(),
        ];
    }
}

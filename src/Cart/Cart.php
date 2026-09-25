<?php

declare(strict_types=1);

namespace Cart;

use Enums\ItemType;
use Exceptions\CartException;
use Support\Messages;

final class Cart
{
    private const MINOR_UNITS_PER_MAJOR = 100;

    private function __construct(private readonly array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public static function fromArray(array $data): self
    {
        InvalidCartPayloadException::unlessHasKeys($data, ['items']);

        if (!is_array($data['items'])) {
            throw InvalidCartPayloadException::malformedItems();
        }

        $items = [];

        foreach ($data['items'] as $raw) {
            $item = self::hydrate($raw);
            $items[$item->key()] = $item;
        }

        return new self($items);
    }

    public function with(CartItem $item): self
    {
        if ($item->type() === ItemType::Accommodation && $this->hasAccommodation()) {
            throw new CartException(Messages::SINGLE_ACCOMMODATION_ONLY);
        }

        return new self([...$this->items, $item->key() => $item]);
    }

    public function without(string $key): self
    {
        if (!array_key_exists($key, $this->items)) {
            throw new CartException(Messages::INVALID_ITEM);
        }

        $items = $this->items;
        unset($items[$key]);

        return new self($items);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function items(): array
    {
        return array_values($this->items);
    }

    public function total(): float
    {
        $sum = 0.0;

        foreach ($this->items as $item) {
            $sum += $item->total();
        }

        return round($sum, 2);
    }

    public function totalMinorUnits(): int
    {
        return (int) round($this->total() * self::MINOR_UNITS_PER_MAJOR);
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(static fn (CartItem $item): array => $item->toArray(), $this->items),
            'total' => $this->total(),
        ];
    }

    private function hasAccommodation(): bool
    {
        foreach ($this->items as $item) {
            if ($item->type() === ItemType::Accommodation) {
                return true;
            }
        }

        return false;
    }

    private static function hydrate(mixed $raw): CartItem
    {
        if (!is_array($raw) || !array_key_exists('type', $raw)) {
            throw InvalidCartPayloadException::malformedItems();
        }

        $type = ItemType::tryFromCartType((string) $raw['type']);

        if ($type === null) {
            throw InvalidCartPayloadException::unknownItemType();
        }

        return match ($type) {
            ItemType::Ticket => TicketItem::fromArray($raw),
            ItemType::Accommodation => AccommodationItem::fromArray($raw),
        };
    }
}

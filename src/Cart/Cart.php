<?php

declare(strict_types=1);

namespace Cart;

use Enums\ItemType;
use Exceptions\Cart\CartException;
use Support\Messages;

final class Cart
{
    /** @param array<string, CartItem> $items */
    private function __construct(private readonly array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @param array<string, mixed> $data */
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

    /** @return list<CartItem> */
    public function items(): array
    {
        return array_values($this->items);
    }

    public function total(): int
    {
        $sum = 0;

        foreach ($this->items as $item) {
            $sum += $item->total();
        }

        return $sum;
    }

    public function totalMinorUnits(): int
    {
        return $this->total();
    }

    /** @return array{items: array<string, array<string, mixed>>, total: int} */
    public function toArray(): array
    {
        return [
            'items' => array_map(static fn (CartItem $item): array => $item->toArray(), $this->items),
            'total' => $this->totalMinorUnits(),
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

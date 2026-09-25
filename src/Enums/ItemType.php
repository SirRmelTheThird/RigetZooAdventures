<?php

declare(strict_types=1);

namespace Enums;

enum ItemType: string
{
    case Ticket = 'Ticket';
    case Accommodation = 'Accommodation';

    public function cartType(): string
    {
        return match ($this) {
            self::Ticket => 'ticket',
            self::Accommodation => 'accommodation',
        };
    }

    public static function tryFromCartType(string $cartType): ?self
    {
        return match ($cartType) {
            'ticket' => self::Ticket,
            'accommodation' => self::Accommodation,
            default => null,
        };
    }

    public function relationship(): string
    {
        return match ($this) {
            self::Ticket => 'ticket',
            self::Accommodation => 'accommodation',
        };
    }
}

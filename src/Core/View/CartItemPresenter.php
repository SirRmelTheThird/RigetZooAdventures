<?php

declare(strict_types=1);

namespace Core\View;

final class CartItemPresenter
{
    private const TYPE_TICKET = 'ticket';
    private const QUANTITY_PREFIX = '× ';
    private const TICKET_TITLE_SUFFIX = ' tickets';


    public static function present(array $item): CartItemView
    {
        if ($item['type'] === self::TYPE_TICKET) {
            return self::ticket($item);
        }

        return self::stay($item);
    }

    private static function ticket(array $item): CartItemView
    {
        $details = [self::detail('Date', (string) $item['date'])];

        if ((int) $item['adult'] > 0) {
            $details[] = self::detail('Adult', self::QUANTITY_PREFIX . (int) $item['adult']);
        }
        if ((int) $item['child'] > 0) {
            $details[] = self::detail('Child', self::QUANTITY_PREFIX . (int) $item['child']);
        }

        return new CartItemView(
            (string) $item['ticketType'] . self::TICKET_TITLE_SUFFIX,
            $details,
            (float) $item['total'],
        );
    }

    private static function stay(array $item): CartItemView
    {
        $nights = Format::count((int) $item['nights'], 'night');

        return new CartItemView(
            (string) $item['name'],
            [
                self::detail('Check-in', (string) $item['startDate']),
                self::detail('Check-out', (string) $item['endDate']),
                self::detail('Stay', $nights . ' at ' . Format::money($item['pricePerNight']) . ' per night'),
                self::detail('Guests', (string) (int) $item['guests']),
            ],
            (float) $item['total'],
        );
    }

    private static function detail(string $label, string $value): array
    {
        return ['label' => $label, 'value' => $value];
    }
}

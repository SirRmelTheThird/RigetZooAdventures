<?php

declare(strict_types=1);

namespace Tests\Unit\Cart;

use Cart\InvalidCartPayloadException;
use Cart\TicketItem;
use Enums\ItemType;
use Enums\TicketType;
use PHPUnit\Framework\TestCase;

final class TicketItemTest extends TestCase
{
    private const TICKET_DATE = '2030-01-15';
    private const TICKET_KEY = 'ticket_Standard_2030-01-15';
    private const ADULT_COUNT = 2;
    private const ADULT_PRICE = 19.99;
    private const CHILD_COUNT = 1;
    private const CHILD_PRICE = 9.99;

    public function testFromArrayCreatesTicketItemAndComputesKeyAndTotal(): void
    {
        $item = TicketItem::fromArray($this->validPayload());

        self::assertSame(ItemType::Ticket, $item->type());
        self::assertSame(TicketType::Standard, $item->ticketType);
        self::assertSame(self::TICKET_KEY, $item->key());
        self::assertSame(self::TICKET_DATE, $item->date);

        $expected = round((self::ADULT_COUNT * self::ADULT_PRICE) + (self::CHILD_COUNT * self::CHILD_PRICE), 2);
        self::assertSame($expected, $item->total());

        $array = $item->toArray();
        self::assertSame('ticket', $array['type']);
        self::assertSame('Standard', $array['ticketType']);
        self::assertSame(self::ADULT_COUNT, $array['adult']);
        self::assertSame(self::CHILD_COUNT, $array['child']);
    }

    public function testFromArrayMissingRequiredKeysThrowsInvalidCartPayloadException(): void
    {
        $payload = $this->validPayload();
        unset($payload['adultPrice']);

        $this->expectException(InvalidCartPayloadException::class);

        TicketItem::fromArray($payload);
    }

    private function validPayload(): array
    {
        return [
            'ticketType' => TicketType::Standard->value,
            'date' => self::TICKET_DATE,
            'adult' => self::ADULT_COUNT,
            'adultPrice' => self::ADULT_PRICE,
            'child' => self::CHILD_COUNT,
            'childPrice' => self::CHILD_PRICE,
        ];
    }
}
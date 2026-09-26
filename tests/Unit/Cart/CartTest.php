<?php

declare(strict_types=1);

namespace Tests\Unit\Cart;

use Cart\Cart;
use Enums\ItemType;
use Exceptions\CartException;
use PHPUnit\Framework\TestCase;
use Tests\Support\CartFixtures;

final class CartTest extends TestCase
{
    use CartFixtures;

    public function testTotalIsDerivedAndMoneyConvertsToCentsWithoutTruncation(): void
    {
        $cart = $this->ticketCart(1, 0);

        self::assertSame($this->ticketAdultPrice(), $cart->total());
        self::assertSame(1999, $cart->totalMinorUnits(), 'intval(19.99*100) would give 1998');
    }

    public function testCartSurvivesTheSessionRoundTrip(): void
    {
        $cart = $this->ticketCart()->with($this->stay());
        $array = $cart->toArray();

        self::assertSame(['items', 'total'], array_keys($array));
        self::assertSame('ticket', $array['items'][$this->ticketKey()]['type']);
        self::assertSame('accommodation', $array['items'][$this->accommodationKey()]['type']);
        self::assertSame($array, Cart::fromArray($array)->toArray());
    }

    public function testOnlyOneAccommodationPerCart(): void
    {
        $this->expectException(CartException::class);
        Cart::empty()->with($this->stay(1))->with($this->stay(2));
    }

    public function testRemovingAnUnknownKeyFailsLoudly(): void
    {
        $this->expectException(CartException::class);
        Cart::empty()->without('nope');
    }

    public function testRemovingAnItemRecomputesTheTotal(): void
    {
        $cart = $this->ticketCart()->with($this->stay());
        $after = $cart->without($this->accommodationKey());

        self::assertSame(round(2 * $this->ticketAdultPrice() + $this->ticketChildPrice(), 2), $after->total());
    }

    public function testItemTypeMapsToDbValuesAndBackToCartDiscriminators(): void
    {
        self::assertSame('Ticket', ItemType::Ticket->value);
        self::assertSame(ItemType::Accommodation, ItemType::tryFromCartType('accommodation'));
        self::assertNull(ItemType::tryFromCartType('Ticket'));
    }
}
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Enums\TicketCategory;
use Enums\TicketType;
use Exceptions\Cart\CartException;
use Exceptions\Http\NotFoundException;
use Models\Tickets\Ticket;
use PHPUnit\Framework\TestCase;
use Repositories\Contracts\Tickets\TicketRepository;
use Services\Tickets\TicketInventory;

final class TicketInventoryTest extends TestCase
{
    public function testReserveLocksTicketAndDecrementsStock(): void
    {
        $repository = $this->createMock(TicketRepository::class);
        $ticket = $this->createStub(Ticket::class);

        $repository
            ->expects(self::once())
            ->method('lockForReservation')
            ->with(TicketType::Standard->value, TicketCategory::Adult)
            ->willReturn($ticket);

        $repository
            ->expects(self::once())
            ->method('decrementAvailableQuantity')
            ->with($ticket, 3);

        $inventory = new TicketInventory($repository);

        self::assertSame($ticket, $inventory->reserve(TicketType::Standard, TicketCategory::Adult, 3));
    }

    public function testReservePropagatesNotFoundExceptionFromLock(): void
    {
        $repository = $this->createMock(TicketRepository::class);

        $repository
            ->expects(self::once())
            ->method('lockForReservation')
            ->willThrowException(new NotFoundException('ticket not found'));

        $inventory = new TicketInventory($repository);

        $this->expectException(NotFoundException::class);
        $inventory->reserve(TicketType::Standard, TicketCategory::Adult, 1);
    }

    public function testReservePropagatesCartExceptionWhenSoldOut(): void
    {
        $repository = $this->createMock(TicketRepository::class);
        $ticket = $this->createStub(Ticket::class);

        $repository
            ->expects(self::once())
            ->method('lockForReservation')
            ->willReturn($ticket);

        $repository
            ->expects(self::once())
            ->method('decrementAvailableQuantity')
            ->willThrowException(new CartException('sold out'));

        $inventory = new TicketInventory($repository);

        $this->expectException(CartException::class);
        $inventory->reserve(TicketType::Standard, TicketCategory::Adult, 1);
    }
}

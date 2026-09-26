<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Enums\TicketCategory;
use Enums\TicketType;
use Exceptions\CartException;
use Exceptions\NotFoundException;
use Models\Ticket;
use PHPUnit\Framework\TestCase;
use Repositories\TicketRepository;
use Services\TicketInventory;

// TicketRepository may not be autoloadable in the current PHPUnit bootstrap.
// Load it explicitly so PHPUnit can generate the mock.
require_once dirname(__DIR__, 3) . '/src/Repositories/TicketRepository.php';

final class TicketInventoryTest extends TestCase
{
    public function testReserveDelegatesToRepository(): void
    {
        $repository = $this->createMock(TicketRepository::class);
        $ticket = $this->createMock(Ticket::class);

        $repository
            ->expects(self::once())
            ->method('reserve')
            ->with(
                TicketType::Standard->value,
                TicketCategory::Adult,
                3,
            )
            ->willReturn($ticket);

        $inventory = new TicketInventory($repository);

        self::assertSame($ticket, $inventory->reserve(TicketType::Standard, TicketCategory::Adult, 3));
    }

    public function testReservePropagatesNotFoundException(): void
    {
        $repository = $this->createMock(TicketRepository::class);

        $repository
            ->expects(self::once())
            ->method('reserve')
            ->willThrowException(new NotFoundException('ticket not found'));

        $inventory = new TicketInventory($repository);

        $this->expectException(NotFoundException::class);
        $inventory->reserve(TicketType::Standard, TicketCategory::Adult, 1);
    }

    public function testReservePropagatesCartException(): void
    {
        $repository = $this->createMock(TicketRepository::class);

        $repository
            ->expects(self::once())
            ->method('reserve')
            ->willThrowException(new CartException('sold out'));

        $inventory = new TicketInventory($repository);

        $this->expectException(CartException::class);
        $inventory->reserve(TicketType::Standard, TicketCategory::Adult, 1);
    }
}

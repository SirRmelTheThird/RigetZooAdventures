<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Cart\Cart;
use Core\Logging\Logger;
use Enums\TicketCategory;
use Enums\TicketType;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Models\Orders\Order;
use Models\Tickets\Ticket;
use PHPUnit\Framework\TestCase;
use Repositories\Contracts\Accommodation\AccommodationRepository;
use Repositories\Contracts\Orders\OrderItemRepository;
use Repositories\Contracts\Orders\OrderRepository;
use Repositories\Contracts\Tickets\TicketRepository;
use Services\Accommodations\AccommodationService;
use Services\Checkout\RewardService;
use Services\Orders\OrderWriter;
use Services\Tickets\BookingService;
use Services\Tickets\TicketInventory;
use Tests\Support\MemoryLogWriter;
use Tests\Support\InMemoryRewardPointRepository;

final class BookingServiceTest extends TestCase
{
    public function testPlacePaidOrderReturnsExistingOrderWhenPaymentAlreadyRecorded(): void
    {
        $existing = new Order();
        $existing->setRawAttributes([
            'id' => 'order-1',
            'stripe_payment_id' => 'pi_123',
        ]);
        $repository = $this->createRepository($existing, null, true);

        $db = $this->createMock(ConnectionInterface::class);
        $db->expects(self::never())->method('transaction');

        $service = new BookingService(
            $db,
            new RewardService(new InMemoryRewardPointRepository()),
            new Logger(new MemoryLogWriter()),
            $repository,
            $this->createOrderWriter($repository),
        );

        self::assertSame('order-1', $service->placePaidOrder('cust-1', Cart::empty(), 'pi_123'));
    }

    public function testPlacePaidOrderRecoversFromUniqueConstraintAndReturnsExistingOrder(): void
    {
        $existing = new Order();
        $existing->setRawAttributes([
            'id' => 'order-1',
            'stripe_payment_id' => 'pi_123',
        ]);
        $repository = $this->createRepository($existing, new UniqueConstraintViolationException(
            'default',
            'insert into orders ...',
            [],
            new \RuntimeException('duplicate key'),
        ));

        $db = $this->createMock(ConnectionInterface::class);
        $db->expects(self::once())
            ->method('transaction')
            ->willReturnCallback(static fn (callable $callback) => $callback());

        $service = new BookingService(
            $db,
            new RewardService(new InMemoryRewardPointRepository()),
            new Logger(new MemoryLogWriter()),
            $repository,
            $this->createOrderWriter($repository),
        );

        self::assertSame('order-1', $service->placePaidOrder('cust-1', Cart::empty(), 'pi_123'));
    }

    private function createRepository(
        Order $existing,
        ?UniqueConstraintViolationException $exception,
        bool $existingAlreadyRecorded = false,
    ): OrderRepository {
        return new class ($existing, $exception, $existingAlreadyRecorded) implements OrderRepository {
            private bool $duplicateDetected = false;

            public function __construct(
                private readonly Order $existing,
                private readonly ?UniqueConstraintViolationException $exception,
                private readonly bool $existingAlreadyRecorded,
            ) {
            }

            public function findById(string $orderId): ?Order
            {
                return $this->existing;
            }

            public function findByStripePaymentId(string $paymentIntentId): ?Order
            {
                if ($this->existingAlreadyRecorded) {
                    return $paymentIntentId === $this->existing->stripe_payment_id ? $this->existing : null;
                }

                if ($this->duplicateDetected) {
                    return $paymentIntentId === $this->existing->stripe_payment_id ? $this->existing : null;
                }

                return null;
            }

            public function findByCustomerId(string $customerId): Collection
            {
                return new Collection();
            }

            public function create(array $data): Order
            {
                if ($this->exception !== null) {
                    $this->duplicateDetected = true;
                    throw $this->exception;
                }

                return $this->existing;
            }

            public function nextOrderNumber(string $customerId): int
            {
                return 1;
            }
        };
    }

    private function createOrderWriter(OrderRepository $repository): OrderWriter
    {
        $orderItems = new class implements OrderItemRepository {
            public function createTicketLine(
                string $orderId,
                string $ticketId,
                int $quantity,
                float $price,
                string $startDate,
            ): void {
            }

            public function createAccommodationLine(
                string $orderId,
                string $accommodationId,
                float $price,
                string $startDate,
                string $endDate,
            ): void {
            }
        };

        $ticketRepository = new class implements TicketRepository {
            public function findById(string $ticketId): ?Ticket
            {
                return null;
            }

            public function findByTicketTypeAndCategory(string $ticketType, TicketCategory $category): ?Ticket
            {
                return null;
            }

            public function lockForReservation(string $ticketType, TicketCategory $category): Ticket
            {
                return new Ticket([
                    'id' => 'ticket-1',
                    'ticket_type' => $ticketType,
                    'category' => $category->value,
                    'available_quantity' => 10,
                ]);
            }

            public function decrementAvailableQuantity(Ticket $ticket, int $quantity): void
            {
            }

            public function listAll(): Collection
            {
                return new Collection();
            }
        };

        $accommodationRepository = new class implements AccommodationRepository {
            public function all(): Collection
            {
                return new Collection();
            }

            public function findById(string $id): ?\Models\Accommodations\Accommodation
            {
                return null;
            }

            public function lockForBooking(string $id, string $startDate, string $endDate): \Models\Accommodations\Accommodation
            {
                return new \Models\Accommodations\Accommodation([
                    'id' => $id,
                    'name' => 'Lodge',
                    'price_per_night' => 100.00,
                    'max_guests' => 4,
                    'available_rooms' => 2,
                ]);
            }

            public function getUnavailableRanges(string $accommodationId): array
            {
                return [];
            }
        };

        return new OrderWriter(
            $repository,
            $orderItems,
            new TicketInventory($ticketRepository),
            new AccommodationService($accommodationRepository),
        );
    }
}
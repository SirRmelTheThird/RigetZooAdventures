<?php

declare(strict_types=1);

namespace Tests\Integration;

use Cart\Cart;
use Core\Logging\Logger;
use Enums\TicketCategory;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Collection;
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
    public function testPlacePaidOrderReturnsTheExistingOrderForTheSamePaymentIntent(): void
    {
        $existing = new Order();
        $existing->setRawAttributes([
            'id' => 'existing-order',
            'stripe_payment_id' => 'pi_456',
        ]);

        $repository = new class ($existing) implements OrderRepository {
            public function __construct(private readonly Order $existing)
            {
            }

            public function findById(string $orderId): ?Order
            {
                return $this->existing;
            }

            public function findByStripePaymentId(string $paymentIntentId): ?Order
            {
                return $paymentIntentId === $this->existing->stripe_payment_id ? $this->existing : null;
            }

            public function findByCustomerId(string $customerId): \Illuminate\Database\Eloquent\Collection
            {
                return new \Illuminate\Database\Eloquent\Collection();
            }

            public function create(array $data): Order
            {
                return $this->existing;
            }

            public function nextOrderNumber(string $customerId): int
            {
                return 1;
            }
        };

        $db = $this->createStub(ConnectionInterface::class);
        $db->method('transaction')->willReturnCallback(static fn (callable $callback) => $callback());

        $service = new BookingService(
            $db,
            new RewardService(new InMemoryRewardPointRepository()),
            new Logger(new MemoryLogWriter()),
            $repository,
            $this->createOrderWriter($repository),
        );

        self::assertSame('existing-order', $service->placePaidOrder('cust-2', Cart::empty(), 'pi_456'));
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

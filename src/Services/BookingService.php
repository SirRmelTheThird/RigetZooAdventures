<?php

declare(strict_types=1);

namespace Services;

use Cart\AccommodationItem;
use Cart\Cart;
use Cart\TicketItem;
use Core\Logging\Logger;
use Enums\ItemType;
use Enums\OrderStatus;
use Enums\TicketCategory;
use Illuminate\Database\ConnectionInterface;
use LogicException;
use Models\Order;
use Repositories\OrderItemRepository;
use Repositories\OrderRepository;

final class BookingService implements OrderPlacer
{
    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly TicketInventory $inventory,
        private readonly AccommodationService $accommodations,
        private readonly RewardService $rewards,
        private readonly Logger $logger,
        private readonly OrderRepository $orders,
        private readonly OrderItemRepository $orderItems,
    ) {
    }

    public function placePaidOrder(int $customerId, Cart $cart, string $paymentIntentId): int
    {
        $existingOrder = $this->orders->findByStripePaymentId($paymentIntentId);

        if ($existingOrder !== null) {
            return (int) $existingOrder->id;
        }

        return $this->db->transaction(fn (): int => $this->createOrder($customerId, $cart, $paymentIntentId));
    }

    private function createOrder(int $customerId, Cart $cart, string $paymentIntentId): int
    {
        $order = $this->buildOrderCore($customerId, $cart, $paymentIntentId);
        return (int) $order->id;
    }

    /**
     * Pure business-logic extraction for focused unit testing (TASK-AUD4-003).
     * No transaction orchestration; returns constructed Order.
     */
    private function buildOrderCore(int $customerId, Cart $cart, string $paymentIntentId): Order
    {
        $order = $this->orders->create([
            'customer_id' => $customerId,
            'total_amount' => $cart->total(),
            'order_status' => OrderStatus::Paid->value,
            'stripe_payment_id' => $paymentIntentId,
        ]);

        foreach ($cart->items() as $item) {
            if ($item instanceof TicketItem) {
                $this->addTicketLine($order, $item, TicketCategory::Adult, $item->adult, $item->adultPrice);
                $this->addTicketLine($order, $item, TicketCategory::Child, $item->child, $item->childPrice);
                continue;
            }

            if ($item instanceof AccommodationItem) {
                $this->addStay($order, $item);
                continue;
            }

            throw new LogicException('Unsupported cart item: ' . $item::class);
        }

        $this->rewards->award($customerId, (int) $order->id, $cart->total());

        $this->logger->info('Order placed', [
            'order_id' => $order->id,
            'customer_id' => $customerId,
            'total' => $cart->total(),
        ]);

        return $order;
    }

    private function addTicketLine(Order $order, TicketItem $item, TicketCategory $category, int $quantity, float $unitPrice): void
    {
        if ($quantity === 0) {
            return;
        }

        $ticket = $this->inventory->reserve($item->ticketType, $category, $quantity);

        $this->orderItems->createTicketLine(
            (int) $order->id,
            (int) $ticket->id,
            $quantity,
            round($quantity * $unitPrice, 2),
            $item->date,
        );
    }

    private function addStay(Order $order, AccommodationItem $item): void
    {
        $accommodation = $this->accommodations->lockForBooking($item->accommodationId, $item->startDate, $item->endDate);

        $this->orderItems->createAccommodationLine(
            (int) $order->id,
            (int) $accommodation->id,
            $item->total(),
            $item->startDate,
            $item->endDate,
        );
    }
}

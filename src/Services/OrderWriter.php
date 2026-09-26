<?php

declare(strict_types=1);

namespace Services;

use Cart\AccommodationItem;
use Cart\Cart;
use Cart\TicketItem;
use Enums\TicketCategory;
use Enums\OrderStatus;
use Exceptions\CartException;
use Models\Order;
use Repositories\Contracts\OrderItemRepository;
use Repositories\Contracts\OrderRepository;

/**
 * Isolates order persistence logic from BookingService orchestration.
 */
final class OrderWriter
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderItemRepository $orderItems,
        private readonly TicketInventory $inventory,
        private readonly AccommodationService $accommodations,
    ) {
    }

    public function writeOrder(string $customerId, Cart $cart, string $paymentIntentId): Order
    {
        $order = $this->orders->create([
            'customer_id' => $customerId,
            'order_number' => $this->orders->nextOrderNumber($customerId),
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

            throw new CartException('Unsupported cart item: ' . $item::class);
        }

        return $order;
    }

    private function addTicketLine(Order $order, TicketItem $item, TicketCategory $category, int $quantity, float $unitPrice): void
    {
        if ($quantity === 0) {
            return;
        }

        $ticket = $this->inventory->reserve($item->ticketType, $category, $quantity);

        $this->orderItems->createTicketLine(
            (string) $order->id,
            (string) $ticket->id,
            $quantity,
            round($quantity * $unitPrice, 2),
            $item->date,
        );
    }

    private function addStay(Order $order, AccommodationItem $item): void
    {
        $accommodation = $this->accommodations->lockForBooking($item->accommodationId, $item->startDate, $item->endDate);

        $this->orderItems->createAccommodationLine(
            (string) $order->id,
            (string) $accommodation->id,
            $item->total(),
            $item->startDate,
            $item->endDate,
        );
    }
}

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
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;
use LogicException;
use Models\Order;
use Models\OrderItem;

final class BookingService implements OrderPlacer
{
    public function __construct(
        private readonly Connection $db,
        private readonly TicketInventory $inventory,
        private readonly AccommodationService $accommodations,
        private readonly RewardService $rewards,
        private readonly Logger $logger,
    ) {
    }

    public function placePaidOrder(int $customerId, Cart $cart, string $paymentIntentId): int
    {
        $existingId = Order::where('stripe_payment_id', $paymentIntentId)->value('id');

        if ($existingId !== null) {
            return (int) $existingId;
        }

        return $this->db->transaction(fn (): int => $this->createOrder($customerId, $cart, $paymentIntentId));
    }

    private function createOrder(int $customerId, Cart $cart, string $paymentIntentId): int
    {
        $order = Order::create([
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

        return (int) $order->id;
    }

    private function addTicketLine(Order $order, TicketItem $item, TicketCategory $category, int $quantity, float $unitPrice): void
    {
        if ($quantity === 0) {
            return;
        }

        $ticket = $this->inventory->reserve($item->ticketType, $category, $quantity);

        OrderItem::create([
            'order_id' => $order->id,
            'item_type' => ItemType::Ticket->value,
            'ticket_id' => $ticket->id,
            'quantity' => $quantity,
            'price' => round($quantity * $unitPrice, 2),
            'start_date' => $item->date,
            'end_date' => null,
        ]);
    }

    private function addStay(Order $order, AccommodationItem $item): void
    {
        $accommodation = $this->accommodations->lockForBooking($item->accommodationId, $item->startDate, $item->endDate);

        OrderItem::create([
            'order_id' => $order->id,
            'item_type' => ItemType::Accommodation->value,
            'accommodation_id' => $accommodation->id,
            'quantity' => 1,
            'price' => $item->total(),
            'start_date' => $item->startDate,
            'end_date' => $item->endDate,
        ]);
    }
}

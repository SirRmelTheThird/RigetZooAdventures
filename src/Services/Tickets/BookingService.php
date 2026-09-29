<?php

declare(strict_types=1);

namespace Services\Tickets;

use Cart\Cart;
use Core\Logging\Logger;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Models\Orders\Order;
use Repositories\Contracts\Orders\OrderRepository;
use Services\Checkout\RewardService;
use Services\Orders\OrderPlacer;
use Services\Orders\OrderWriter;

final class BookingService implements OrderPlacer
{
    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly RewardService $rewards,
        private readonly Logger $logger,
        private readonly OrderRepository $orders,
        private readonly OrderWriter $writer,
    ) {
    }

    public function placePaidOrder(string $customerId, Cart $cart, string $paymentIntentId): string
    {
        $existingOrder = $this->orders->findByStripePaymentId($paymentIntentId);

        if ($existingOrder !== null) {
            return (string) $existingOrder->id;
        }

        try {
            return $this->db->transaction(fn (): string => $this->createOrder($customerId, $cart, $paymentIntentId));
        } catch (UniqueConstraintViolationException $e) {
            $this->logger->info('Duplicate paid order detected, returning existing order', [
                'payment_intent_id' => $paymentIntentId,
                'customer_id' => $customerId,
            ]);

            $existingOrder = $this->orders->findByStripePaymentId($paymentIntentId);

            if ($existingOrder !== null) {
                return (string) $existingOrder->id;
            }

            throw $e;
        }
    }

    private function createOrder(string $customerId, Cart $cart, string $paymentIntentId): string
    {
        $order = $this->buildOrderCore($customerId, $cart, $paymentIntentId);
        return (string) $order->id;
    }

    private function buildOrderCore(string $customerId, Cart $cart, string $paymentIntentId): Order
    {
        $order = $this->writer->writeOrder($customerId, $cart, $paymentIntentId);

        $this->rewards->award($customerId, (string) $order->id, $cart->total());

        $this->logger->info('Order placed', [
            'order_id' => $order->id,
            'customer_id' => $customerId,
            'total' => $cart->total(),
        ]);

        return $order;
    }
}

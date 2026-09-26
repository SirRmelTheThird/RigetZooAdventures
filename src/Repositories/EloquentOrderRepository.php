<?php

declare(strict_types=1);

namespace  Repositories;

use Illuminate\Database\Eloquent\Collection;
use Models\Order;

final class EloquentOrderRepository implements OrderRepository
{
    public function findById(int $orderId): ?Order
    {
        return Order::find($orderId);
    }

    public function findByStripePaymentId(string $paymentIntentId): ?Order
    {
        $order = Order::where('stripe_payment_id', $paymentIntentId)->first();
        return $order;
    }

    public function findByCustomerId(int $customerId): Collection
    {
        return Order::where('customer_id', $customerId)->get();
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }
}

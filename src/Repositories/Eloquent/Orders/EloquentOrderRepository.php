<?php

declare(strict_types=1);

namespace Repositories\Eloquent\Orders;

use Illuminate\Database\Eloquent\Collection;
use Models\Orders\Order;
use Repositories\Contracts\Orders\OrderRepository;

final class EloquentOrderRepository implements OrderRepository
{
    public function findById(string $orderId): ?Order
    {
        return Order::find($orderId);
    }

    public function findByStripePaymentId(string $paymentIntentId): ?Order
    {
        $order = Order::where('stripe_payment_id', $paymentIntentId)->first();
        return $order;
    }

    public function findByCustomerId(string $customerId): Collection
    {
        return Order::where('customer_id', $customerId)->get();
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

  public function nextOrderNumber(string $customerId): int
  {
      return (int) Order::where('customer_id', $customerId)
          ->max('order_number') + 1;
  }
}

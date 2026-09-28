<?php

declare(strict_types=1);

namespace Repositories\Contracts\Orders;

use Illuminate\Database\Eloquent\Collection;
use Models\Orders\Order;

interface OrderRepository
{
    public function findById(string $orderId): ?Order;
    public function findByStripePaymentId(string $paymentIntentId): ?Order;
    public function findByCustomerId(string $customerId): Collection;
    public function create(array $data): Order;
    public function nextOrderNumber(string $customerId): int;
}

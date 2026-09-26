<?php

declare(strict_types=1);

namespace Repositories\Contracts;

use Models\Order;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepository
{
    public function findById(string $orderId): ?Order;
    public function findByStripePaymentId(string $paymentIntentId): ?Order;
    public function findByCustomerId(string $customerId): Collection;
    public function create(array $data): Order;
    public function nextOrderNumber(string $customerId): int;
}

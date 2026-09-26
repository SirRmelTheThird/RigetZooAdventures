<?php

declare(strict_types=1);

namespace Repositories;

use Models\Order;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepository
{
    public function findById(int $orderId): ?Order;

    public function findByStripePaymentId(string $paymentIntentId): ?Order;

    public function findByCustomerId(int $customerId): Collection;

    public function create(array $data): Order;
}

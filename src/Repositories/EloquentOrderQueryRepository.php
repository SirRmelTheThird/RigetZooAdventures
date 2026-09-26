<?php

declare(strict_types=1);

namespace Repositories;

use Illuminate\Database\Eloquent\Collection;
use Models\Order;

final class EloquentOrderQueryRepository implements OrderQueryRepository
{
    public function ordersForCustomerWithItems(int $customerId): Collection
    {
        return Order::where('customer_id', $customerId)
            ->with('items')
            ->orderByDesc('created_at')
            ->get();
    }
}

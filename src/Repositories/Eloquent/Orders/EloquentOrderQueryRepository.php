<?php

declare(strict_types=1);

namespace Repositories\Eloquent\Orders;

use Illuminate\Database\Eloquent\Collection;
use Models\Order;
use Repositories\Contracts\OrderQueryRepository;

final class EloquentOrderQueryRepository implements OrderQueryRepository
{
    public function ordersForCustomerWithItems(string $customerId): Collection
    {
        return Order::where('customer_id', $customerId)
            ->with('items')
            ->orderByDesc('created_at')
            ->get();
    }
}

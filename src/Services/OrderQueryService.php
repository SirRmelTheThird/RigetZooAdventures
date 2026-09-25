<?php

declare(strict_types=1);

namespace Services;

use Illuminate\Database\Eloquent\Collection;
use Models\Order;
use Models\OrderItem;

final class OrderQueryService
{
    public function __construct(
        private readonly OrderItemLoader $itemLoader,
    ) {
    }

    public function ordersFor(int $customerId): Collection
    {
        $orders = Order::where('customer_id', $customerId)
            ->with('items')
            ->orderByDesc('created_at')
            ->get();

        $items = new Collection(
            $orders->flatMap(fn (Order $order) => $order->items)
            ->all()
        );

        $this->itemLoader->load($items);

        return $orders;
    }
}

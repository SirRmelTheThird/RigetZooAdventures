<?php

declare(strict_types=1);

namespace Services\Orders;

use Illuminate\Database\Eloquent\Collection;
use Repositories\Contracts\Orders\OrderQueryRepository;

final class OrderQueryService
{
    public function __construct(
        private readonly OrderQueryRepository $orders,
        private readonly OrderItemLoader $itemLoader,
    ) {
    }

    public function ordersFor(string $customerId): Collection
    {
        $orders = $this->orders->ordersForCustomerWithItems($customerId);
        $items = new Collection($orders->pluck('items')->collapse());
        $this->itemLoader->load($items);
        return $orders;
    }
}

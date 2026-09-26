<?php

declare(strict_types=1);

namespace Services;

use Illuminate\Database\Eloquent\Collection;
use Models\Order;
use Models\OrderItem;
use Repositories\OrderQueryRepository;

final class OrderQueryService
{
    public function __construct(
        private readonly OrderQueryRepository $orders,
        private readonly OrderItemLoader $itemLoader,
    ) {
    }

    /**
     * @return Collection<int, Order>
     */
    public function ordersFor(int $customerId): Collection
    {
        $orders = $this->orders->ordersForCustomerWithItems($customerId);

        $items = new Collection($orders->pluck('items')->collapse());

        $this->itemLoader->load($items);

        return $orders;
    }
}

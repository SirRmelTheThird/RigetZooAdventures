<?php

declare(strict_types=1);

namespace Repositories;

use Illuminate\Database\Eloquent\Collection;
use Models\Order;

interface OrderQueryRepository
{
    /** @return Collection<int, Order> */
    public function ordersForCustomerWithItems(int $customerId): Collection;
}

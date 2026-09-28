<?php

declare(strict_types=1);

namespace Repositories\Contracts\Orders;

use Illuminate\Database\Eloquent\Collection;
use Models\Orders\Order;

interface OrderQueryRepository
{
    public function ordersForCustomerWithItems(string $customerId): Collection;
}

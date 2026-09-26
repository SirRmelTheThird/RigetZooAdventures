<?php

declare(strict_types=1);

namespace Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Models\Order;

interface OrderQueryRepository
{
    public function ordersForCustomerWithItems(string $customerId): Collection;
}

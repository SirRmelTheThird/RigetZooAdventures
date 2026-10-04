<?php

declare(strict_types=1);

namespace Repositories\Contracts\Orders;

use Illuminate\Database\Eloquent\Collection;

interface OrderQueryRepository
{
    public function ordersForCustomerWithItems(string $customerId): Collection;
}

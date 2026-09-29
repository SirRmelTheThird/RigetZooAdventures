<?php

declare(strict_types=1);

namespace Tests\Integration;

use Illuminate\Database\Eloquent\Collection;
use Models\Orders\Order;
use PHPUnit\Framework\TestCase;
use Repositories\Contracts\Orders\OrderQueryRepository;
use Services\Orders\OrderItemLoader;
use Services\Orders\OrderQueryService;

final class OrderQueryServiceTest extends TestCase
{
    public function testOrdersForReturnsTheCustomerOrdersAndLoadsTheirItems(): void
    {
        $order = new Order();
        $order->setRawAttributes(['id' => 'order-1', 'customer_id' => 'customer-1']);
        $orders = new Collection([$order]);
        $repository = new class ($orders) implements OrderQueryRepository {
            public function __construct(private readonly Collection $orders)
            {
            }

            public function ordersForCustomerWithItems(string $customerId): Collection
            {
                return $this->orders;
            }
        };

        $result = (new OrderQueryService($repository, new OrderItemLoader()))->ordersFor('customer-1');

        self::assertSame($orders, $result);
        self::assertSame('order-1', $result->first()->id);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use Enums\OrderStatus;
use Models\Orders\Order;
use PHPUnit\Framework\TestCase;

final class OrderStatusTest extends TestCase
{
    public function testPaidValueIsLowercasePaid(): void
    {
        self::assertSame('paid', OrderStatus::Paid->value);
    }

    public function testAllOrderStatusValuesMatchSchemaConvention(): void
    {
        self::assertSame('pending', OrderStatus::Pending->value);
        self::assertSame('paid', OrderStatus::Paid->value);
        self::assertSame('cancelled', OrderStatus::Cancelled->value);
        self::assertSame('failed', OrderStatus::Failed->value);
    }

    public function testOrderCastConvertsOrderStatusStringsToEnum(): void
    {
        $order = new Order();

        $order->order_status = 'pending';
        self::assertSame(OrderStatus::Pending, $order->order_status);

        $order->order_status = 'paid';
        self::assertSame(OrderStatus::Paid, $order->order_status);

        $order->order_status = 'cancelled';
        self::assertSame(OrderStatus::Cancelled, $order->order_status);

        $order->order_status = 'failed';
        self::assertSame(OrderStatus::Failed, $order->order_status);
    }

    public function testOrderCastStoresEnumValuesAsSchemaLowercaseStrings(): void
    {
        $order = new Order();

        $order->order_status = OrderStatus::Paid;
        self::assertSame('paid', $order->getAttributes()['order_status']);

        $order->order_status = OrderStatus::Failed;
        self::assertSame('failed', $order->getAttributes()['order_status']);
    }
}

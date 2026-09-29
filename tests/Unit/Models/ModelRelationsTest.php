<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Models\Accommodations\Accommodation;
use Models\Accommodations\AccommodationAvailability;
use Models\Auth\Customer;
use Models\Orders\Order;
use Models\Orders\OrderItem;
use Models\Rewards\RewardPoint;
use Models\Tickets\Ticket;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Query\Builder;

final class ModelRelationsTest extends TestCase
{
    protected function setUp(): void
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('query')->willReturn(new Builder($connection, new Grammar(), new Processor()));

        $resolver = $this->createStub(DatabaseManager::class);
        $resolver->method('connection')->willReturn($connection);
        EloquentModel::setConnectionResolver($resolver);
    }

    public function testCustomerRelationsResolveToTheirDomainModels(): void
    {
        $customer = new Customer();

        self::assertInstanceOf(Order::class, $customer->orders()->getRelated());
        self::assertInstanceOf(RewardPoint::class, $customer->rewardPoints()->getRelated());
    }

    public function testOrderRelationsResolveToTheirDomainModels(): void
    {
        $order = new Order();

        self::assertInstanceOf(Customer::class, $order->customer()->getRelated());
        self::assertInstanceOf(OrderItem::class, $order->items()->getRelated());
    }

    public function testOrderItemRelationsResolveToTheirDomainModels(): void
    {
        $item = new OrderItem();

        self::assertInstanceOf(Order::class, $item->order()->getRelated());
        self::assertInstanceOf(Ticket::class, $item->ticket()->getRelated());
        self::assertInstanceOf(Accommodation::class, $item->accommodation()->getRelated());
    }

    public function testCatalogRelationsResolveToTheirDomainModels(): void
    {
        self::assertInstanceOf(OrderItem::class, (new Ticket())->orderItems()->getRelated());
        self::assertInstanceOf(OrderItem::class, (new Accommodation())->orderItems()->getRelated());
        self::assertInstanceOf(AccommodationAvailability::class, (new Accommodation())->availabilityRanges()->getRelated());
        self::assertInstanceOf(Accommodation::class, (new AccommodationAvailability())->accommodation()->getRelated());
    }

    public function testRewardPointRelationsResolveToTheirDomainModels(): void
    {
        $rewardPoint = new RewardPoint();

        self::assertInstanceOf(Customer::class, $rewardPoint->customer()->getRelated());
        self::assertInstanceOf(Order::class, $rewardPoint->order()->getRelated());
    }
}

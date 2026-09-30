<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Models\Rewards\RewardPoint;
use PHPUnit\Framework\TestCase;
use Repositories\Contracts\Rewards\RewardPointRepository;
use Services\Checkout\RewardService;

final class RewardServiceTest extends TestCase
{
    public function testPointsForMapsPoundsToRoundedPence(): void
    {
        $service = new RewardService(new class () implements RewardPointRepository {
            public function create(array $data): RewardPoint
            {
                return new RewardPoint($data);
            }
        });

        self::assertSame(0, $service->pointsFor(0.0));

        $points = $service->pointsFor(19.99);
        self::assertSame(1999, $points);

        $points2 = $service->pointsFor(1.004);
        self::assertSame(100, $points2);
    }

    public function testAwardPersistsPositivePointsThroughRepository(): void
    {
        $created = [];
        $repository = new class ($created) implements RewardPointRepository {
            public function __construct(private array &$created)
            {
            }

            public function create(array $data): RewardPoint
            {
                $this->created[] = $data;

                return new RewardPoint($data);
            }
        };

        (new RewardService($repository))->award('customer-1', 'order-1', 19.99);

        self::assertSame([
            'customer_id' => 'customer-1',
            'order_id' => 'order-1',
            'points' => 1999,
            'transaction_type' => 'earned',
            'description' => 'Points earned from order #order-1',
        ], $created[0]);
    }

    public function testAwardDoesNotPersistNonPositivePoints(): void
    {
        $repository = $this->createMock(RewardPointRepository::class);
        $repository->expects(self::never())->method('create');

        (new RewardService($repository))->award('customer-1', 'order-1', 0.0);
    }
}

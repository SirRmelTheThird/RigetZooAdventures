<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use Services\RewardService;

final class RewardServiceTest extends TestCase
{
    public function testPointsForMapsPoundsToRoundedPence(): void
    {
        $service = new RewardService();

        self::assertSame(0, $service->pointsFor(0.0));

        $points = $service->pointsFor(19.99);
        self::assertSame(1999, $points);

        $points2 = $service->pointsFor(1.004);
        self::assertSame(100, $points2);
    }
}

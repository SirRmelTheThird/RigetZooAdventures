<?php

declare(strict_types=1);

namespace Services;

use Models\RewardPoint;

final class RewardService
{
    private const POINTS_PER_POUND = 10;
    private const PENCE_PER_POUND = 100;
    private const TRANSACTION_EARNED = 'earned';

    public function pointsFor(float $total): int
    {
        return intdiv((int) round($total * self::PENCE_PER_POUND) * self::POINTS_PER_POUND, self::PENCE_PER_POUND);
    }

    public function award(int $customerId, int $orderId, float $total): void
    {
        $points = $this->pointsFor($total);

        if ($points === 0) {
            return;
        }

        RewardPoint::create([
            'customer_id' => $customerId,
            'order_id' => $orderId,
            'points' => $points,
            'transaction_type' => self::TRANSACTION_EARNED,
            'description' => "Points earned from order #{$orderId}",
        ]);
    }
}

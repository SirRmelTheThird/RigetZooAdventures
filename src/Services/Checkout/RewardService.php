<?php

declare(strict_types=1);

namespace Services\Checkout;

use Repositories\Contracts\Rewards\RewardPointRepository;

final class RewardService
{
    private const POINTS_PER_POUND = 100;
    private const TRANSACTION_EARNED = 'earned';

    public function __construct(private readonly RewardPointRepository $rewardPoints)
    {
    }

    public function pointsFor(float $total): int
    {
        return (int) round($total * self::POINTS_PER_POUND);
    }

    public function award(string $customerId, string $orderId, float $total): void
    {
        $points = $this->pointsFor($total);

        if ($points <= 0) {
            return;
        }

        $this->rewardPoints->create([
            'customer_id' => $customerId,
            'order_id' => $orderId,
            'points' => $points,
            'transaction_type' => self::TRANSACTION_EARNED,
            'description' => "Points earned from order #{$orderId}",
        ]);
    }
}

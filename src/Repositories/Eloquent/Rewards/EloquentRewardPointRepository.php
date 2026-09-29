<?php

declare(strict_types=1);

namespace Repositories\Eloquent\Rewards;

use Models\Rewards\RewardPoint;
use Repositories\Contracts\Rewards\RewardPointRepository;

final class EloquentRewardPointRepository implements RewardPointRepository
{
    public function create(array $data): RewardPoint
    {
        return RewardPoint::create($data);
    }
}

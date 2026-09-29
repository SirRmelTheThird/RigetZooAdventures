<?php

declare(strict_types=1);

namespace Repositories\Contracts\Rewards;

use Models\Rewards\RewardPoint;

interface RewardPointRepository
{
    /** @param array<string, mixed> $data */
    public function create(array $data): RewardPoint;
}

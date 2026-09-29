<?php

declare(strict_types=1);

namespace Tests\Support;

use Models\Rewards\RewardPoint;
use Repositories\Contracts\Rewards\RewardPointRepository;

final class InMemoryRewardPointRepository implements RewardPointRepository
{
    /** @var array<int, array<string, mixed>> */
    public array $created = [];

    public function create(array $data): RewardPoint
    {
        $this->created[] = $data;

        return new RewardPoint($data);
    }
}

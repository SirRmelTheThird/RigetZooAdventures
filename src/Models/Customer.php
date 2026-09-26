<?php

declare(strict_types=1);

namespace Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasUuids;

    private const BCRYPT_COST = 12;

    protected $table = 'customers';

    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function rewardPoints(): HasMany
    {
        return $this->hasMany(RewardPoint::class, 'customer_id');
    }

    public function totalRewardPoints(): int
    {
        return (int) $this->rewardPoints()->sum('points');
    }
}

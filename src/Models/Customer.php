<?php

declare(strict_types=1);

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    private const BCRYPT_COST = 12;

    protected $table = 'customers';

    protected $fillable = ['first_name', 'last_name', 'username', 'email', 'password'];

    protected $hidden = ['password'];

    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_BCRYPT, ['cost' => self::BCRYPT_COST]);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function rewardPoints()
    {
        return $this->hasMany(RewardPoint::class, 'customer_id');
    }

    public function totalRewardPoints(): int
    {
        return (int) $this->rewardPoints()->sum('points');
    }
}

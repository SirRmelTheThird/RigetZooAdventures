<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password'
    ];

    protected $hidden = [
        'password'
    ];

    // Automatically hash password when setting
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    // Verify password
    public function verifyPassword($plainPassword)
    {
        return password_verify($plainPassword, $this->password);
    }

    // Relationships
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function rewardPoints()
    {
        return $this->hasMany(RewardPoint::class, 'customer_id');
    }

    public function totalRewardPoints()
    {
        return $this->rewardPoints()->sum('points');
    }

    // Static helper methods
    public static function findByUsername($username)
    {
        return static::where('username', $username)->first();
    }

    public static function findByEmail($email)
    {
        return static::where('email', $email)->first();
    }

    public static function usernameExists($username)
    {
        return static::where('username', $username)->exists();
    }

    public static function emailExists($email)
    {
        return static::where('email', $email)->exists();
    }
}
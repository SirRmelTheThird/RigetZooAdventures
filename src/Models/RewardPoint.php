<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class RewardPoint extends Model
{
    protected $table = 'reward_points';

    protected $fillable = [
        'customer_id',
        'points',
        'order_id',
        'transaction_type',
        'description'
    ];

    protected $casts = [
        'points' => 'integer'
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // Static helper methods
    public static function calculatePoints($totalAmount)
    {
        // 10 points per dollar
        return intval($totalAmount * 10);
    }

    public static function awardPoints($customerId, $points, $orderId = null, $description = null)
    {
        return static::create([
            'customer_id' => $customerId,
            'points' => $points,
            'order_id' => $orderId,
            'transaction_type' => 'earned',
            'description' => $description ?? "Points earned from order #{$orderId}"
        ]);
    }

    public static function getCustomerBalance($customerId)
    {
        return static::where('customer_id', $customerId)->sum('points');
    }
}
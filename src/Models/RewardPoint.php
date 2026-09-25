<?php

declare(strict_types=1);

namespace Models;

use Illuminate\Database\Eloquent\Model;

class RewardPoint extends Model
{
    protected $table = 'reward_points';

    protected $fillable = ['customer_id', 'points', 'order_id', 'transaction_type', 'description'];

    protected $casts = ['points' => 'integer'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}

<?php

declare(strict_types=1);

namespace Models;

use Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder<Order>
 */
class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = ['customer_id', 'total_amount', 'order_status', 'stripe_payment_id'];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function isPaid(): bool
    {
        return $this->order_status === OrderStatus::Paid->value;
    }

    public function isPending(): bool
    {
        return $this->order_status === OrderStatus::Pending->value;
    }
}

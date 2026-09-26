<?php

declare(strict_types=1);

namespace Models;

use Enums\OrderStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasUuids;

    protected $table = 'orders';

    protected $fillable = [
        'customer_id',
        'order_number',
        'total_amount',
        'order_status',
        'stripe_payment_id',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'order_status' => OrderStatus::class,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function isPaid(): bool
    {
        return $this->order_status === OrderStatus::Paid;
    }

    public function isPending(): bool
    {
        return $this->order_status === OrderStatus::Pending;
    }
}
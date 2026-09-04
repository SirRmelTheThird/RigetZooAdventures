<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'customer_id',
        'total_amount',
        'order_status',
        'stripe_payment_id'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('order_status', 'Paid');
    }

    public function scopePending($query)
    {
        return $query->where('order_status', 'Pending');
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    // Helper methods
    public function isPaid()
    {
        return $this->order_status === 'Paid';
    }

    public function isPending()
    {
        return $this->order_status === 'Pending';
    }

    public function markAsPaid($stripePaymentId = null)
    {
        $this->order_status = 'Paid';
        if ($stripePaymentId) {
            $this->stripe_payment_id = $stripePaymentId;
        }
        $this->save();
    }

    public function markAsCancelled()
    {
        $this->order_status = 'Cancelled';
        $this->save();
    }
}
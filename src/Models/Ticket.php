<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $fillable = [
        'type',
        'category',
        'price',
        'description',
        'available_quantity'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'available_quantity' => 'integer'
    ];

    // Relationships
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'ticket_id');
    }

    // Scopes
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOfCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeStandard($query)
    {
        return $query->where('type', 'Standard');
    }

    public function scopePremium($query)
    {
        return $query->where('type', 'Premium');
    }

    public function scopeAdult($query)
    {
        return $query->where('category', 'Adult');
    }

    public function scopeChild($query)
    {
        return $query->where('category', 'Child');
    }

    // Helper methods
    public function isAvailable($quantity = 1)
    {
        return $this->available_quantity >= $quantity;
    }

    public function decreaseQuantity($quantity)
    {
        $this->available_quantity -= $quantity;
        $this->save();
    }

    public function increaseQuantity($quantity)
    {
        $this->available_quantity += $quantity;
        $this->save();
    }
}
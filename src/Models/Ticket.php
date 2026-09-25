<?php

declare(strict_types=1);

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $fillable = ['type', 'category', 'price', 'description', 'available_quantity'];

    protected $casts = [
        'price' => 'decimal:2',
        'available_quantity' => 'integer',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'ticket_id');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function isAvailable(int $quantity = 1): bool
    {
        return $this->available_quantity >= $quantity;
    }
}

<?php

declare(strict_types=1);

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    protected $table = 'accommodations';

    protected $fillable = [
        'name',
        'description',
        'price_per_night',
        'max_guests',
        'available_rooms',
        'location',
        'image_url',
    ];

    protected $casts = [
        'price_per_night' => 'decimal:2',
        'max_guests' => 'integer',
        'available_rooms' => 'integer',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'accommodation_id');
    }
}

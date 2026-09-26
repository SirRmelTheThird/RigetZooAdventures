<?php

declare(strict_types=1);

namespace Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Accommodation extends Model
{
    use HasUuids;

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

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'price_per_night' => 'decimal:2',
            'max_guests' => 'integer',
            'available_rooms' => 'integer',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'accommodation_id');
    }

    public function availabilityRanges(): HasMany
    {
        return $this->hasMany(AccommodationAvailability::class, 'accommodation_id');
    }
}
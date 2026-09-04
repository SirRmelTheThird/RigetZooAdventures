<?php

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
        'image_url'
    ];

    protected $casts = [
        'price_per_night' => 'decimal:2',
        'max_guests' => 'integer',
        'available_rooms' => 'integer'
    ];

    // Relationships
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'accommodation_id');
    }

    // Availability checking
    public function isAvailable($startDate, $endDate)
    {
        // Check if there are any overlapping bookings
        $overlappingBookings = $this->orderItems()
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                    });
            })
            ->whereHas('order', function ($query) {
                $query->where('order_status', '!=', 'Cancelled');
            })
            ->count();

        return $overlappingBookings < $this->available_rooms;
    }

    public function getBookedRooms($startDate, $endDate)
    {
        return $this->orderItems()
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                    });
            })
            ->whereHas('order', function ($query) {
                $query->where('order_status', '!=', 'Cancelled');
            })
            ->count();
    }

    public function calculateNights($startDate, $endDate)
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $diff = $start->diff($end);

        return $diff->days;
    }

    public function calculatePrice($startDate, $endDate)
    {
        $nights = $this->calculateNights($startDate, $endDate);
        return $this->price_per_night * $nights;
    }
}
<?php

declare(strict_types=1);

namespace Models;

use Enums\ItemType;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'item_type',
        'ticket_id',
        'accommodation_id',
        'quantity',
        'start_date',
        'end_date',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'quantity' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class, 'accommodation_id');
    }

    public function isTicket(): bool
    {
        return $this->item_type === ItemType::Ticket->value;
    }

    public function isAccommodation(): bool
    {
        return $this->item_type === ItemType::Accommodation->value;
    }

    public function getItem(): ?Model
    {
        if ($this->isTicket()) {
            return $this->ticket;
        }

        return $this->accommodation;
    }
}

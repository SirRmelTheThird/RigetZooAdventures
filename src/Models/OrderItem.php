<?php

declare(strict_types=1);

namespace Models;

use Enums\ItemType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasUuids;

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

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'item_type' => ItemType::class,
            'price' => 'decimal:2',
            'quantity' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class, 'accommodation_id');
    }

    public function isTicket(): bool
    {
        return $this->item_type === ItemType::Ticket;
    }

    public function isAccommodation(): bool
    {
        return $this->item_type === ItemType::Accommodation;
    }

    public function getItem(): Ticket|Accommodation|null
    {
        if ($this->isTicket()) {
            return $this->ticket;
        }

        if ($this->isAccommodation()) {
            return $this->accommodation;
        }

        return null;
    }
}
<?php

declare(strict_types=1);

namespace Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Ticket extends Model
{
    use HasUuids;

    protected $table = 'tickets';

    protected $fillable = [
        'type',
        'category',
        'price',
        'description',
        'available_quantity',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'available_quantity' => 'integer',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'ticket_id');
    }

    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    public function scopeOfCategory(Builder $query, string $category): void
    {
        $query->where('category', $category);
    }

    public function isAvailable(int $quantity = 1): bool
    {
        return $quantity > 0 && $this->available_quantity >= $quantity;
    }
}

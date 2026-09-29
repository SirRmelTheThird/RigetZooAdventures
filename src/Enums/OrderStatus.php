<?php

declare(strict_types=1);

namespace Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
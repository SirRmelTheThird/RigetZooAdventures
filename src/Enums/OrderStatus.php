<?php

declare(strict_types=1);

namespace Enums;

enum OrderStatus: string
{
    case Pending = 'Pending';
    case Paid = 'Paid';
    case Cancelled = 'Cancelled';
    case Failed = 'Failed';
}

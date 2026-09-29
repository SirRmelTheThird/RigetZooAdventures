<?php

declare(strict_types=1);

namespace Enums;

enum TicketType: string
{
    case Standard = 'standard';
    case Premium = 'premium';
}

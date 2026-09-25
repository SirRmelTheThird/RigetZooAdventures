<?php

declare(strict_types=1);

namespace Core\View;

enum TicketCategory: string
{
    case Adult = 'adult';
    case Child = 'child';
}

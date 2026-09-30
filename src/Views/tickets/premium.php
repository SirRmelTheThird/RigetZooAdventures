<?php

declare(strict_types=1);

use Contracts\TicketsContentInterface;
use Services\Tickets\TicketPricing;
use Core\View\View;

/**
 * @var \Illuminate\Database\Eloquent\Collection<int, \Models\Ticket> $tickets
 */

$pageTitle = 'Premium Tickets';
require __DIR__ . '/../layouts/header.php';
$content = View::content(TicketsContentInterface::class);

$tiers = $content->getTiers();

View::partial('catalog/ticket-booking', [
    'tier' => $tiers['premium'],
    'pricing' => TicketPricing::fromTickets($tickets),
    'ages' => $content->getAges(),
    'booking' => $content->getBooking(),
]);

require __DIR__ . '/../layouts/footer.php';

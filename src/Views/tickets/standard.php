<?php

declare(strict_types=1);

use Contracts\TicketsContentInterface;
use Services\Tickets\TicketPricing;
use Core\View\View;

/**
 * @var \Illuminate\Database\Eloquent\Collection<int, \Models\Ticket> $tickets
 */


error_log('CHECKPOINT 1');
$pageTitle = 'Standard Tickets';
error_log('CHECKPOINT 2 - after header');
require __DIR__ . '/../layouts/header.php';
$content = View::content(TicketsContentInterface::class);
error_log('CHECKPOINT 3 - after content');

$tiers = $content->getTiers();
error_log('CHECKPOINT 4 - after tiers');

View::partial('catalog/ticket-booking', [
    'tier' => $tiers['standard'],
    'pricing' => TicketPricing::fromTickets($tickets),
    'ages' => $content->getAges(),
    'booking' => $content->getBooking(),
]);

require __DIR__ . '/../layouts/footer.php';

error_log('CHECKPOINT 6 - after partial');
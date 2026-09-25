<?php

declare(strict_types=1);

use Core\View\TicketPricing;
use Core\View\View;

$pageTitle = 'Premium tickets';
require __DIR__ . '/../layouts/header.php';

$content = View::content('tickets');

View::partial('ticket-booking', [
    'tier' => $content['tiers']['premium'],
    'pricing' => TicketPricing::fromTickets($tickets),
    'ages' => $content['ages'],
    'booking' => $content['booking'],
]);

require __DIR__ . '/../layouts/footer.php';

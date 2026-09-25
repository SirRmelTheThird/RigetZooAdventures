<?php

declare(strict_types=1);

use Core\View\TicketPricing;
use Core\View\View;

$pageTitle = 'Tickets';
require __DIR__ . '/../layouts/header.php';

$content = View::content('tickets');
$standardPricing = TicketPricing::fromTickets($standardTickets);
$premiumPricing = TicketPricing::fromTickets($premiumTickets);
?>

<div class="rz-container rz-page">
    <?php View::partial('page-header', ['header' => $content['index']]); ?>

    <div class="rz-tiers">
        <?php View::partial('ticket-tier', ['tier' => $content['tiers']['standard'], 'pricing' => $standardPricing, 'ages' => $content['ages']]); ?>
        <?php View::partial('ticket-tier', ['tier' => $content['tiers']['premium'], 'pricing' => $premiumPricing, 'ages' => $content['ages']]); ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

<?php

declare(strict_types=1);

use App\Models\TicketsContentInterface;
use Core\View\TicketPricing;
use Core\View\View;

$pageTitle = 'Tickets';
require __DIR__ . '/../layouts/header.php';

$content = View::content(TicketsContentInterface::class);
$standardPricing = TicketPricing::fromTickets($standardTickets);
$premiumPricing = TicketPricing::fromTickets($premiumTickets);
?>

<div class="rz-container rz-page">
    <?php View::partial('page-header', ['header' => $content->getIndex()]); ?>

    <div class="rz-tiers">
        <?php View::partial('ticket-tier', ['tier' => $content->getTiers()['standard'], 'pricing' => $standardPricing, 'ages' => $content->getAges()]); ?>
        <?php View::partial('ticket-tier', ['tier' => $content->getTiers()['premium'], 'pricing' => $premiumPricing, 'ages' => $content->getAges()]); ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
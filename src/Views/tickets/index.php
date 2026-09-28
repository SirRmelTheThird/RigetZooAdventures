<?php

declare(strict_types=1);

use Contracts\TicketsContentInterface;
use Core\View\TicketPricing;
use Core\View\View;

/**
 * @var array $standardTickets
 * @var array $premiumTickets
 */

$pageTitle = 'Tickets';
require __DIR__ . '/../layouts/header.php';

$content = View::content(TicketsContentInterface::class);
$standardPricing = TicketPricing::fromTickets($standardTickets);
$premiumPricing = TicketPricing::fromTickets($premiumTickets);

?>

<div class="rz-container rz-page">
    <?php View::partial('layout/page-header', ['header' => $content->getIndex()]); ?>

    <div class="rz-tiers">
        <?php View::partial('catalog/ticket-tier', ['tier' => $content->getTiers()['standard'], 'pricing' => $standardPricing, 'ages' => $content->getAges()]); ?>
        <?php View::partial('catalog/ticket-tier', ['tier' => $content->getTiers()['premium'], 'pricing' => $premiumPricing, 'ages' => $content->getAges()]); ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
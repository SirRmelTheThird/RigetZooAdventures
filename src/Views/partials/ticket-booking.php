<?php

declare(strict_types=1);

use Core\CSRF;
use Core\View\Format;
use Core\View\TicketPricing;
use Core\View\View;

/**
 * Booking screen shared by the Standard and Premium tiers.
 *
 * @var array<string, mixed>                 $tier    entry from content/tickets.php
 * @var TicketPricing                        $pricing
 * @var array<string, array<string, string>> $ages
 * @var array<string, string>                $booking labels for the form
 */
$page = $tier['page'];
$pills = [
    $ages['adult']['price_label'] . ' ' . Format::money($pricing->adult),
    $ages['child']['price_label'] . ' ' . Format::money($pricing->child),
    $page['extra_pill'],
];
?>
<div class="rz-container rz-page">
    <?php View::partial('page-header', ['header' => $page]); ?>

    <div class="rz-booking">
        <aside class="rz-booking__aside rz-reveal">
            <?php View::partial('media', $page['media'] + ['ratio' => '4x5']); ?>
            <?php View::partial('pills', ['items' => $pills]); ?>
        </aside>

        <section class="rz-card rz-card--raised rz-booking__form rz-reveal" data-ticket-form data-total-target="ticket-total">
            <h2><?= Format::e($page['form_title']) ?></h2>
            <p class="rz-muted"><?= Format::e($page['form_intro']) ?></p>

            <form action="<?= Format::e($tier['href']) ?>" method="POST">
                <?= CSRF::field() ?>

                <?php View::partial('quantity-stepper', ['age' => $ages['adult'], 'price' => $pricing->adult]); ?>
                <?php View::partial('quantity-stepper', ['age' => $ages['child'], 'price' => $pricing->child]); ?>

                <?php if ($page['callout'] !== null): ?>
                    <div class="rz-callout">
                        <strong><?= Format::e($page['callout']['title']) ?></strong>
                        <p><?= Format::e($page['callout']['text']) ?></p>
                    </div>
                <?php endif; ?>

                <?php
                View::partial('form-field', [
                    'id' => $page['date_id'],
                    'name' => 'date',
                    'label' => $booking['date_label'],
                    'type' => 'date',
                    'attrs' => ['min' => Format::isoDate()],
                ]);
?>

                <div class="rz-total">
                    <span><?= Format::e($booking['total_label']) ?></span>
                    <strong id="ticket-total" data-total>£0.00</strong>
                </div>

                <button type="submit" class="rz-btn rz-btn--primary rz-btn--block" data-submit disabled>
                    <?= Format::e($booking['submit_label']) ?>
                </button>
            </form>
        </section>
    </div>
</div>

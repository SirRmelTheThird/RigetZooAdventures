<?php

declare(strict_types=1);

use Core\View\Format;
use Core\View\TicketPricing;
use Core\View\View;

/**
 * One admission tier on the ticket picker.
 *
 * @var array<string, mixed>                 $tier    entry from content/tickets.php
 * @var TicketPricing                        $pricing
 * @var array<string, array<string, string>> $ages
 */
$card = $tier['card'];
?>
<article class="rz-tier<?= Format::when($card['featured'], ' rz-tier--featured') ?> rz-reveal">
    <h2><?= Format::e($card['title']) ?></h2>
    <p class="rz-tier__text"><?= Format::e($card['text']) ?></p>

    <dl class="rz-prices">
        <div>
            <dt><?= Format::e($ages['adult']['price_label']) ?></dt>
            <dd><?= Format::money($pricing->adult) ?></dd>
        </div>
        <div>
            <dt><?= Format::e($ages['child']['price_label']) ?></dt>
            <dd><?= Format::money($pricing->child) ?></dd>
        </div>
    </dl>

    <?php View::partial('check-list', ['items' => $card['includes']]); ?>

    <a class="rz-btn rz-btn--block <?= Format::when($card['featured'], 'rz-btn--primary') ?><?= Format::when(!$card['featured'], 'rz-btn--secondary') ?>" href="<?= Format::e($tier['href']) ?>">
        <?= Format::e($card['cta']) ?>
    </a>
</article>

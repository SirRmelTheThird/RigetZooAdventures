<?php

declare(strict_types=1);

use Core\View\CartItemView;
use Core\View\Format;

/**
 * Compact, read-only order review used beside the payment form.
 *
 * @var string                $title
 * @var list<CartItemView>    $items
 * @var array<string, string> $labels  summary labels (items_label, total_label)
 * @var float                 $total
 */
$metaSeparator = ' · ';
?>
<aside class="rz-summary rz-review rz-reveal" aria-label="<?= Format::e($title) ?>">
    <h2><?= Format::e($title) ?></h2>

    <ul class="rz-review__items">
        <?php foreach ($items as $item): ?>
            <li>
                <div class="rz-review__row">
                    <strong><?= Format::e($item->title) ?></strong>
                    <span><?= Format::money($item->total) ?></span>
                </div>
                <p class="rz-review__meta">
                    <?= Format::e(implode($metaSeparator, array_map(
                        static fn (array $detail): string => $detail['label'] . ' ' . $detail['value'],
                        $item->details,
                    ))) ?>
                </p>
            </li>
        <?php endforeach; ?>
    </ul>

    <dl>
        <div class="rz-summary__row">
            <dt><?= Format::e($labels['items_label']) ?></dt>
            <dd><?= count($items) ?></dd>
        </div>
        <div class="rz-summary__row rz-summary__row--strong">
            <dt><?= Format::e($labels['total_label']) ?></dt>
            <dd><?= Format::money($total) ?></dd>
        </div>
    </dl>
</aside>

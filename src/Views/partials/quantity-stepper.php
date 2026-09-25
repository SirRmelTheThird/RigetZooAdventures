<?php

declare(strict_types=1);

use Core\View\Format;

/**
 * Plus/minus control. The hidden input carries the value the server reads.
 *
 * @var array{field: string, title: string, hint: string} $age
 * @var float                                             $price price for one ticket
 */
?>
<div class="rz-stepper" data-stepper data-price="<?= Format::e($price) ?>">
    <div class="rz-stepper__label">
        <strong><?= Format::e($age['title']) ?></strong>
        <span><?= Format::e($age['hint']) ?> · <?= Format::money($price) ?> each</span>
    </div>
    <div class="rz-stepper__controls">
        <button type="button" class="rz-stepper__btn" data-step="-1" aria-label="Remove one <?= Format::e(strtolower($age['title'])) ?>">-</button>
        <output class="rz-stepper__value" data-value>0</output>
        <input type="hidden" name="<?= Format::e($age['field']) ?>" value="0" data-input>
        <button type="button" class="rz-stepper__btn" data-step="1" aria-label="Add one <?= Format::e(strtolower($age['title'])) ?>">+</button>
    </div>
</div>

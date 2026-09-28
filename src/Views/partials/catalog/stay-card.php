<?php

declare(strict_types=1);

use Core\Constants\RedirectKey;
use Core\CSRF;
use Core\View\Format;
use Core\View\View;

/**
 * @var object $accommodation
 * @var array<string, string> $copy
 * @var array<int, array{start_date: string, end_date: string}> $unavailable default []
 */

$id = (string) $accommodation->id;
$unavailable = $unavailable ?? [];
$pills = [];
if (!empty($accommodation->location)) {
    $pills[] = $accommodation->location;
}
$pills[] = $copy['max_guests_prefix'] . $accommodation->max_guests . ' guests';

$imageUrl = (string) $accommodation->image_url;
$unavailableJson = json_encode($unavailable, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);

// Compute outer bounds for min/max
$allStarts = array_column($unavailable, 'start_date');
$allEnds = array_column($unavailable, 'end_date');
$globalMin = $allStarts ? min($allStarts) : Format::isoDate();
$globalMax = $allEnds ? max($allEnds) : null;
if ($globalMax !== null && $globalMax <= Format::isoDate()) {
    $globalMax = null;
}
?>
<article class="rz-stay rz-reveal">
    <?php
    View::partial('layout/media', [
        'src' => $imageUrl,
        'alt' => $accommodation->name,
        'icon' => $copy['media_icon'],
        'ratio' => 'fill',
    ]);
?>

    <div class="rz-stay__body">
        <h2><?= Format::e($accommodation->name) ?></h2>
        <?php View::partial('forms/pills', ['items' => $pills]); ?>
        <p class="rz-muted"><?= Format::e($accommodation->description) ?></p>
        <p class="rz-price"><?= Format::money($accommodation->price_per_night) ?> <small><?= Format::e($copy['per_night']) ?></small></p>


    <form class="rz-stay__form" action="<?= RedirectKey::ACCOMMODATIONS_ADD ?>" method="POST" data-date-range
          data-msg-date-unavailable="<?= Format::e($copy['date_unavailable']) ?>"
          data-msg-range-unavailable="<?= Format::e($copy['range_unavailable']) ?>">
        <?= CSRF::field() ?>

        <input type="hidden" name="id" value="<?= Format::e($id) ?>">

        <div class="rz-stay__dates">
            <?php View::partial('forms/form-field', [
                'id' => 'start_date_' . $id,
                'name' => 'start_date',
                'label' => $copy['check_in'],
                'type' => 'date',
                'attrs' => [
                    'min' => Format::isoDate(),
                    'max' => $globalMax ?? '',
                    'data-range-start' => '',
                    'data-unavailable' => $unavailableJson,
                ],
            ]); ?>

            <?php View::partial('forms/form-field', [
                'id' => 'end_date_' . $id,
                'name' => 'end_date',
                'label' => $copy['check_out'],
                'type' => 'date',
                'attrs' => [
                    'min' => Format::isoDate('+1 day'),
                    'max' => $globalMax ?? '',
                    'data-range-end' => '',
                ],
            ]); ?>
        </div>

        <div class="rz-stay__booking">
            <div class="rz-field rz-stay__guests">
                <label for="guests_<?= Format::e($id) ?>">
                    <?= Format::e($copy['guests']) ?>
                </label>

                <select id="guests_<?= Format::e($id) ?>" name="guests" required>
                    <?php for ($guests = 1; $guests <= (int) $accommodation->max_guests; $guests++): ?>
                        <option value="<?= $guests ?>">
                            <?= Format::count($guests, $copy['guest_noun']) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <button type="submit" class="rz-btn rz-btn--primary rz-stay__reserve">
                <?= Format::e($copy['submit_label']) ?>
            </button>
          </div>
      </form>
    </div>
</article>

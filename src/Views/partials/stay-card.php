<?php

declare(strict_types=1);

use Core\Constants\RedirectKey;
use Core\CSRF;
use Core\View\Format;
use Core\View\View;

/**
 * One bookable accommodation.
 *
 * @var object                $accommodation Eloquent Accommodation
 * @var array<string, string> $copy          content/stays.php "card" block
 */
$id = (int) $accommodation->id;
$pills = [];
if (!empty($accommodation->location)) {
    $pills[] = $accommodation->location;
}
$pills[] = $copy['max_guests_prefix'] . $accommodation->max_guests . ' guests';

$imageUrl = (string) $accommodation->image_url;
?>
<article class="rz-stay rz-reveal">
    <?php
    View::partial('media', [
        'src' => $imageUrl,
        'alt' => $accommodation->name,
        'icon' => $copy['media_icon'],
        'ratio' => 'fill',
    ]);
?>

    <div class="rz-stay__body">
        <h2><?= Format::e($accommodation->name) ?></h2>
        <?php View::partial('pills', ['items' => $pills]); ?>
        <p class="rz-muted"><?= Format::e($accommodation->description) ?></p>
        <p class="rz-price"><?= Format::money($accommodation->price_per_night) ?> <small><?= Format::e($copy['per_night']) ?></small></p>

        <form class="rz-stay__form" action="<?= RedirectKey::ACCOMMODATIONS_ADD ?>" method="POST" data-date-range>
            <?= CSRF::field() ?>
            <input type="hidden" name="id" value="<?= $id ?>">

            <?php
        View::partial('form-field', [
            'id' => 'start_date_' . $id,
            'name' => 'start_date',
            'label' => $copy['check_in'],
            'type' => 'date',
            'attrs' => ['min' => Format::isoDate(), 'data-range-start' => ''],
        ]);
View::partial('form-field', [
    'id' => 'end_date_' . $id,
    'name' => 'end_date',
    'label' => $copy['check_out'],
    'type' => 'date',
    'attrs' => ['min' => Format::isoDate('+1 day'), 'data-range-end' => ''],
]);
?>

            <div class="rz-field">
                <label for="guests_<?= $id ?>"><?= Format::e($copy['guests']) ?></label>
                <select id="guests_<?= $id ?>" name="guests" required>
                    <?php for ($guests = 1; $guests <= (int) $accommodation->max_guests; $guests++): ?>
                        <option value="<?= $guests ?>"><?= Format::count($guests, $copy['guest_noun']) ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <button type="submit" class="rz-btn rz-btn--primary"><?= Format::e($copy['submit_label']) ?></button>
        </form>
    </div>
</article>

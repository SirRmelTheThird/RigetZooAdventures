<?php
require __DIR__ . '/../layouts/header.php';
use Core\CSRF;
?>

<div class="page-content">
    <header class="page-header reveal">
        <p class="page-kicker">Overnight stays</p>
        <h1>Stay inside the park.</h1>
        <p class="page-subtitle">Choose a lodge, hotel room, or glamping stay close to the habitats and visitor routes.</p>
    </header>

    <div class="accommodation-grid">
        <?php foreach ($accommodations as $accommodation): ?>
            <article class="accommodation-card content-card reveal">
                <div class="media-frame">
                    <img src="<?= htmlspecialchars($accommodation->image_url ?? '/assets/images/placeholder.jpg') ?>"
                         alt="<?= htmlspecialchars($accommodation->name) ?>"
                         onerror="this.closest('.media-frame').classList.add('is-missing'); this.remove();">
                    <span class="media-fallback"><?= htmlspecialchars($accommodation->name) ?></span>
                </div>

                <div class="accommodation-card-body">
                    <h2><?= htmlspecialchars($accommodation->name) ?></h2>

                    <div class="stat-row">
                        <?php if (!empty($accommodation->location)): ?>
                            <span class="stat-pill"><?= htmlspecialchars($accommodation->location) ?></span>
                        <?php endif; ?>
                        <span class="stat-pill">Up to <?= $accommodation->max_guests ?> guests</span>
                    </div>

                    <p><?= htmlspecialchars($accommodation->description) ?></p>

                    <p class="price-line">
                        $<?= number_format((float)$accommodation->price_per_night, 2) ?> <small>per night</small>
                    </p>

                    <form action="/accommodations/add" method="POST">
                        <?= CSRF::field() ?>
                        <input type="hidden" name="id" value="<?= $accommodation->id ?>">

                        <div class="form-field">
                            <label for="start_date_<?= $accommodation->id ?>">Check-in</label>
                            <input type="date" id="start_date_<?= $accommodation->id ?>" name="start_date" required min="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="form-field">
                            <label for="end_date_<?= $accommodation->id ?>">Check-out</label>
                            <input type="date" id="end_date_<?= $accommodation->id ?>" name="end_date" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>

                        <div class="form-field">
                            <label for="guests_<?= $accommodation->id ?>">Guests</label>
                            <select id="guests_<?= $accommodation->id ?>" name="guests" required>
                                <?php for ($i = 1; $i <= $accommodation->max_guests; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?> Guest<?= $i > 1 ? 's' : '' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <button type="submit" class="submit">Book Stay</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

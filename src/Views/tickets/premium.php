<?php
require __DIR__ . '/../layouts/header.php';
use Core\CSRF;
?>

<div class="page-content">
    <header class="page-header reveal">
        <p class="page-kicker">Premium admission</p>
        <h1>Unlock the full safari route.</h1>
        <p class="page-subtitle">Premium tickets add guided experiences and expanded access for visitors who want the complete day.</p>
    </header>

    <div class="booking-layout">
        <aside class="content-card reveal">
            <div class="media-frame" style="min-height: 320px;">
                <img src="/assets/images/seal.jpg" alt="Seal habitat at Riget Zoo Adventures" onerror="this.closest('.media-frame').classList.add('is-missing'); this.remove();">
                <span class="media-fallback">Premium Ticket</span>
            </div>
            <div class="stat-row">
                <span class="stat-pill">Adult $50</span>
                <span class="stat-pill">Child $40</span>
                <span class="stat-pill">Guided access</span>
            </div>
        </aside>

        <section class="booking-card reveal">
            <h2>Premium Tickets</h2>
            <p>Choose your group size and date. Premium access includes drive-through safari, walking safari, and boat safari experiences.</p>

            <form action="/tickets/premium" method="POST">
                <?= CSRF::field() ?>

                <div class="options">
                    <div class="option-label">
                        <strong>Adult Tickets</strong>
                        <span>Ages 16 and over</span>
                    </div>
                    <div class="quantity-controls">
                        <button type="button" class="quantity-btn" id="sub_adult" aria-label="Remove adult ticket">-</button>
                        <span class="quantity-value" id="adult_value">0</span>
                        <input type="hidden" name="adult" id="adult_input" value="0">
                        <button type="button" class="quantity-btn" id="add_adult" aria-label="Add adult ticket">+</button>
                    </div>
                </div>

                <div class="options">
                    <div class="option-label">
                        <strong>Child Tickets</strong>
                        <span>Ages 3 to 15</span>
                    </div>
                    <div class="quantity-controls">
                        <button type="button" class="quantity-btn" id="sub_child" aria-label="Remove child ticket">-</button>
                        <span class="quantity-value" id="child_value">0</span>
                        <input type="hidden" name="child" id="child_input" value="0">
                        <button type="button" class="quantity-btn" id="add_child" aria-label="Add child ticket">+</button>
                    </div>
                </div>

                <div class="content-card" style="margin: 22px 0; padding: 18px; background: var(--orange-100);">
                    <strong>Premium includes</strong>
                    <p style="margin: 8px 0 0;">Drive-through safari, walking safari, boat safari, experienced guides, and education programs.</p>
                </div>

                <div class="form-field">
                    <label for="premium_visit_date">Visit Date</label>
                    <input type="date" id="premium_visit_date" name="date" required min="<?= date('Y-m-d') ?>">
                </div>

                <button type="submit" class="submit">Add to Cart</button>
            </form>
        </section>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

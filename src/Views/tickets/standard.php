<?php
require __DIR__ . '/../layouts/header.php';
use Core\CSRF;
?>

<div class="page-content">
    <header class="page-header reveal">
        <p class="page-kicker">Standard admission</p>
        <h1>Plan a classic zoo day.</h1>
        <p class="page-subtitle">A straightforward pass for habitats, facilities, food stops, and a full day at Riget Zoo Adventures.</p>
    </header>

    <div class="booking-layout">
        <aside class="content-card reveal">
            <div class="media-frame" style="min-height: 320px;">
                <img src="/assets/images/giraffe.jpg" alt="Giraffe habitat at Riget Zoo Adventures" onerror="this.closest('.media-frame').classList.add('is-missing'); this.remove();">
                <span class="media-fallback">Standard Ticket</span>
            </div>
            <div class="stat-row">
                <span class="stat-pill">Adult $25</span>
                <span class="stat-pill">Child $20</span>
                <span class="stat-pill">Infants free</span>
            </div>
        </aside>

        <section class="booking-card reveal">
            <h2>Standard Tickets</h2>
            <p>Select your group size and visit date. You can review the total before checkout.</p>

            <form action="/tickets/standard" method="POST">
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

                <div class="form-field" style="margin-top: 22px;">
                    <label for="standard_visit_date">Visit Date</label>
                    <input type="date" id="standard_visit_date" name="date" required min="<?= date('Y-m-d') ?>">
                </div>

                <button type="submit" class="submit">Add to Cart</button>
            </form>
        </section>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

<?php
require __DIR__ . '/layouts/header.php';
use Core\CSRF;
use Core\Session;
?>

<div class="page-content">
    <header class="page-header reveal">
        <p class="page-kicker">Checkout</p>
        <h1>Complete your booking.</h1>
        <p class="page-subtitle">Confirm your visit details, then use the secure payment form to place the order.</p>
    </header>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger reveal"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="checkout-layout">
        <section class="cart-items">
            <h2>Review Your Order</h2>

            <?php foreach ($cart['items'] as $item): ?>
                <article class="cart-item reveal">
                    <?php if ($item['type'] === 'ticket'): ?>
                        <h4><?= htmlspecialchars($item['ticketType']) ?> Tickets</h4>
                        <p><strong>Date:</strong> <?= htmlspecialchars($item['date']) ?></p>
                        <?php if ($item['adult'] > 0): ?>
                            <p>Adult x <?= $item['adult'] ?></p>
                        <?php endif; ?>
                        <?php if ($item['child'] > 0): ?>
                            <p>Child x <?= $item['child'] ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <h4><?= htmlspecialchars($item['name']) ?></h4>
                        <p><?= htmlspecialchars($item['startDate']) ?> to <?= htmlspecialchars($item['endDate']) ?></p>
                        <p><?= $item['nights'] ?> nights, <?= $item['guests'] ?> guests</p>
                    <?php endif; ?>
                    <p class="price-line" style="font-size: 1.5rem;">$<?= number_format($item['total'], 2) ?></p>
                </article>
            <?php endforeach; ?>

            <section class="content-card reveal">
                <h3>Payment Information</h3>
                <p>Use a Stripe test card in development. The payment keys are loaded from the environment only.</p>

                <form action="/payment/process" method="POST">
                    <?= CSRF::field() ?>
                    <?php if (!empty($clientSecret)): ?>
                        <input type="hidden" name="payment_intent_id" value="<?= htmlspecialchars(Session::get('payment_intent_id', '')) ?>">
                    <?php endif; ?>

                    <div class="form-field">
                        <label for="card_holder">Card Holder Name</label>
                        <input id="card_holder" type="text" class="form-control" name="card_holder" placeholder="Name on card" required>
                    </div>

                    <div class="form-field">
                        <label for="card_number">Card Number</label>
                        <input id="card_number" type="text" class="form-control" name="card_number" placeholder="4242 4242 4242 4242" autocomplete="cc-number" required>
                    </div>

                    <div class="grid" style="grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px;">
                        <div class="form-field">
                            <label for="expiry_date">Expiry Date</label>
                            <input id="expiry_date" type="text" class="form-control" name="expiry_date" placeholder="MM/YY" autocomplete="cc-exp" required>
                        </div>

                        <div class="form-field">
                            <label for="cvv">CVV</label>
                            <input id="cvv" type="text" class="form-control" name="cvv" placeholder="123" autocomplete="cc-csc" required>
                        </div>
                    </div>

                    <button type="submit" class="submit">Complete Payment</button>
                </form>
            </section>
        </section>

        <aside class="cart-summary reveal">
            <h3>Order Summary</h3>

            <div class="summary-row">
                <span>Subtotal</span>
                <strong>$<?= number_format($cart['total'], 2) ?></strong>
            </div>

            <div class="summary-row">
                <span>Tax</span>
                <strong>$0.00</strong>
            </div>

            <div class="summary-row">
                <span>Total</span>
                <strong>$<?= number_format($cart['total'], 2) ?></strong>
            </div>

            <div class="summary-row">
                <span>Points to Earn</span>
                <strong><?= intval($cart['total'] * 10) ?></strong>
            </div>

            <div class="content-card" style="margin-top: 20px; padding: 16px; background: rgba(255, 253, 248, 0.10); color: var(--surface);">
                <p style="margin: 0; color: rgba(255, 253, 248, 0.82);"><strong style="color: var(--surface);">Logged in as:</strong><br><?= htmlspecialchars(Session::getUsername()) ?></p>
            </div>
        </aside>
    </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>

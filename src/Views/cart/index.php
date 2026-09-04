<?php
require __DIR__ . '/../layouts/header.php';
use Core\CSRF;
?>

<div class="page-content">
    <header class="page-header reveal">
        <p class="page-kicker">Your visit</p>
        <h1>Shopping Cart</h1>
        <p class="page-subtitle">Review tickets and stays before moving to checkout.</p>
    </header>

    <?php if (empty($cart['items'])): ?>
        <section class="empty-state reveal">
            <span class="material-symbols-outlined" style="font-size: 72px;" aria-hidden="true">shopping_cart</span>
            <h2>Your cart is empty</h2>
            <p>Start with admission tickets, then add an overnight stay if you want more time in the park.</p>
            <div class="hero-actions" style="justify-content: center; margin-top: 18px;">
                <a href="/tickets" class="btn-primary-rza">Browse Tickets</a>
                <a href="/accommodations" class="btn-secondary-rza">View Stays</a>
            </div>
        </section>
    <?php else: ?>
        <div class="cart-container">
            <section class="cart-items">
                <h2>Your Items</h2>

                <?php foreach ($cart['items'] as $key => $item): ?>
                    <article class="cart-item reveal">
                        <?php if ($item['type'] === 'ticket'): ?>
                            <h4><?= htmlspecialchars($item['ticketType']) ?> Tickets</h4>
                            <p><strong>Date:</strong> <?= htmlspecialchars($item['date']) ?></p>
                            <?php if ($item['adult'] > 0): ?>
                                <p>Adult x <?= $item['adult'] ?> at $<?= number_format($item['adultPrice'], 2) ?> each: $<?= number_format($item['adult'] * $item['adultPrice'], 2) ?></p>
                            <?php endif; ?>
                            <?php if ($item['child'] > 0): ?>
                                <p>Child x <?= $item['child'] ?> at $<?= number_format($item['childPrice'], 2) ?> each: $<?= number_format($item['child'] * $item['childPrice'], 2) ?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <h4><?= htmlspecialchars($item['name']) ?></h4>
                            <p><strong>Check-in:</strong> <?= htmlspecialchars($item['startDate']) ?></p>
                            <p><strong>Check-out:</strong> <?= htmlspecialchars($item['endDate']) ?></p>
                            <p><?= $item['nights'] ?> nights at $<?= number_format($item['pricePerNight'], 2) ?> per night</p>
                            <p><strong>Guests:</strong> <?= $item['guests'] ?></p>
                        <?php endif; ?>

                        <p class="price-line" style="font-size: 1.5rem;">$<?= number_format($item['total'], 2) ?></p>

                        <form action="/cart/remove" method="POST">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="key" value="<?= htmlspecialchars($key) ?>">
                            <button type="submit" class="btn btn-secondary">Remove Item</button>
                        </form>
                    </article>
                <?php endforeach; ?>

                <div>
                    <a href="/tickets" class="btn-secondary-rza">Continue Shopping</a>
                </div>
            </section>

            <aside class="cart-summary reveal">
                <h3>Order Summary</h3>

                <div class="summary-row">
                    <span>Items</span>
                    <strong><?= count($cart['items']) ?></strong>
                </div>

                <div class="summary-row">
                    <span>Total</span>
                    <strong>$<?= number_format($cart['total'], 2) ?></strong>
                </div>

                <div class="summary-row">
                    <span>Points to Earn</span>
                    <strong><?= $points ?></strong>
                </div>

                <form action="/checkout" method="GET" style="margin-top: 20px;">
                    <button type="submit" class="submit">Proceed to Checkout</button>
                </form>

                <form action="/cart/clear" method="POST" style="margin-top: 12px;">
                    <?= CSRF::field() ?>
                    <button type="submit" id="clear-cart" class="btn btn-secondary" style="width: 100%;">Clear Cart</button>
                </form>
            </aside>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

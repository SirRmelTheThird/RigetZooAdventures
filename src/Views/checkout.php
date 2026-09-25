<?php

declare(strict_types=1);

use Core\Constants\RedirectKey;
use Core\CSRF;
use Core\View\CartItemPresenter;
use Core\View\Format;
use Core\View\View;

$pageTitle = 'Checkout';
require __DIR__ . '/layouts/header.php';

$content = View::content('checkout');
$payment = $content['payment'];
$items = array_map([CartItemPresenter::class, 'present'], $cart['items']);
?>

<div class="rz-container rz-page">
    <?php View::partial('page-header', ['header' => $content['header']]); ?>

    <div class="rz-checkout rz-checkout--pay">
        <div>
            <?php if (!empty($error)): ?>
                <?php View::partial('alert', ['tone' => 'error', 'messages' => [$error]]); ?>
            <?php endif; ?>

            <?php if (!empty($clientSecret)): ?>
                <section class="rz-card rz-card--raised rz-reveal" aria-labelledby="payment-title">
                    <h2 id="payment-title"><?= Format::e($payment['title']) ?></h2>
                    <p class="rz-muted"><?= Format::e($payment['text']) ?></p>

                    <form id="payment-form" action="<?= RedirectKey::PAYMENT_PROCESS ?>" method="POST" data-stripe-key="<?= Format::e($stripePublishableKey) ?>" data-client-secret="<?= Format::e($clientSecret) ?>" data-failure-message="<?= Format::e($payment['failure_message']) ?>">
                        <?= CSRF::field() ?>
                        <div id="payment-element"></div>
                        <div id="payment-message" class="rz-alert rz-alert--error" role="alert" hidden></div>
                        <button id="payment-submit" type="submit" class="rz-btn rz-btn--primary rz-btn--block"><?= Format::e($payment['submit_label']) ?> · <?= Format::money($cart['total']) ?></button>
                    </form>
                </section>
            <?php endif; ?>
        </div>

        <?php
        View::partial('order-review', [
            'title' => $content['summary']['title'],
            'items' => $items,
            'labels' => $content['summary'],
            'total' => (float) $cart['total'],
        ]);
?>
    </div>
</div>

<?php if (!empty($clientSecret)): ?>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="/assets/js/checkout.js"></script>
<?php endif; ?>

<?php require __DIR__ . '/layouts/footer.php'; ?>

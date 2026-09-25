<?php

declare(strict_types=1);

use Core\Constants\RedirectKey;
use Core\CSRF;
use Core\View\CartItemPresenter;
use Core\View\Format;
use Core\View\View;

$pageTitle = 'Cart';
require __DIR__ . '/../layouts/header.php';

$content = View::content('cart');
$labels = $content['summary'];
?>

<div class="rz-container rz-page">
    <?php View::partial('page-header', ['header' => $content['header']]); ?>

    <?php if (empty($cart['items'])): ?>
        <?php
        View::partial('empty-state', [
            'icon' => $content['empty']['icon'],
            'title' => $content['empty']['title'],
            'text' => $content['empty']['text'],
            'actions' => [
                $content['empty']['primary'] + ['variant' => 'primary'],
                $content['empty']['secondary'] + ['variant' => 'secondary'],
            ],
        ]);
        ?>
    <?php else: ?>
        <div class="rz-checkout">
            <section class="rz-lines" aria-labelledby="cart-items-title">
                <h2 id="cart-items-title"><?= Format::e($content['items_title']) ?></h2>

                <?php foreach ($cart['items'] as $key => $item): ?>
                    <?php
                    View::partial('cart-item', [
                        'view' => CartItemPresenter::present($item),
                        'remove' => ['key' => (string) $key, 'label' => $content['remove_label']],
                    ]);
                    ?>
                <?php endforeach; ?>

                <a class="rz-btn rz-btn--secondary rz-btn--sm" href="<?= Format::e($content['continue']['href']) ?>"><?= Format::e($content['continue']['label']) ?></a>
            </section>

            <?php ob_start(); ?>
                <form action="<?= RedirectKey::CHECKOUT ?>" method="GET">
                    <button type="submit" class="rz-btn rz-btn--primary rz-btn--block"><?= Format::e($labels['checkout_label']) ?></button>
                </form>
                <form action="<?= RedirectKey::CART_CLEAR ?>" method="POST" data-confirm="<?= Format::e($content['clear_confirm']) ?>">
                    <?= CSRF::field() ?>
                    <button type="submit" class="rz-btn rz-btn--light rz-btn--block"><?= Format::e($labels['clear_label']) ?></button>
                </form>
            <?php $actionsHtml = ob_get_clean(); ?>

            <?php
            View::partial('summary-panel', [
                'title' => $labels['title'],
                'rows' => [
                    ['label' => $labels['items_label'], 'value' => (string) count($cart['items'])],
                    ['label' => $labels['total_label'], 'value' => Format::money($cart['total']), 'strong' => true],
                    ['label' => $labels['points_label'], 'value' => (string) $points],
                ],
                'actionsHtml' => $actionsHtml,
            ]);
        ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

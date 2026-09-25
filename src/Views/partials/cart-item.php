<?php

declare(strict_types=1);

use Core\CSRF;
use Core\Constants\RedirectKey;
use Core\View\CartItemView;
use Core\View\Format;

/**
 * One row in the cart or checkout review.
 *
 * @var CartItemView                             $view
 * @var array{key: string, label: string}|null   $remove null on checkout, where rows are read-only
 */
?>
<article class="rz-line rz-reveal">
    <div class="rz-line__main">
        <h3><?= Format::e($view->title) ?></h3>
        <dl class="rz-line__details">
            <?php foreach ($view->details as $detail): ?>
                <div>
                    <dt><?= Format::e($detail['label']) ?></dt>
                    <dd><?= Format::e($detail['value']) ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </div>

    <div class="rz-line__side">
        <p class="rz-line__price"><?= Format::money($view->total) ?></p>
        <?php if ($remove !== null): ?>
            <form action="<?= RedirectKey::CART_REMOVE ?>" method="POST">
                <?= CSRF::field() ?>
                <input type="hidden" name="key" value="<?= Format::e($remove['key']) ?>">
                <button type="submit" class="rz-link-btn">
                    <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                    <?= Format::e($remove['label']) ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</article>

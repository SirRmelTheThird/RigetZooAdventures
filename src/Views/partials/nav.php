<?php

declare(strict_types=1);

use Core\Constants\RedirectKey;
use Core\CSRF;
use Core\View\Format;
use Core\View\View;

/**
 * @var array<string, mixed>       $site
 * @var list<array<string, mixed>> $navItems
 * @var bool                       $isLoggedIn
 * @var string                     $username
 * @var int                        $cartCount
 */
?>
<header class="rz-nav" id="nav">
    <div class="rz-container rz-nav__inner">
        <a class="rz-brand" href="<?= RedirectKey::HOME ?>" aria-label="<?= Format::e($site['name']) ?> home">
            <img class="rz-brand__logo" src="<?= Format::e($site['logo']) ?>" alt="" width="30" height="30" data-hide-on-error>
            <span><?= Format::e($site['name']) ?></span>
        </a>

        <button class="rz-nav__toggle" type="button" data-bs-toggle="collapse" data-bs-target="#primaryNavigation" aria-controls="primaryNavigation" aria-expanded="false" aria-label="Toggle navigation">
            <span class="material-symbols-outlined" aria-hidden="true">menu</span>
        </button>

        <nav class="rz-nav__menu collapse" id="primaryNavigation" aria-label="Primary">
            <ul class="rz-nav__list">
                <?php foreach ($navItems as $item): ?>
                    <?php View::partial('nav-item', ['item' => $item]); ?>
                <?php endforeach; ?>
            </ul>

            <ul class="rz-nav__list rz-nav__list--account">
                <?php if ($isLoggedIn): ?>
                    <li class="rz-nav__item dropdown">
                        <button type="button" class="rz-nav__link" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="material-symbols-outlined" aria-hidden="true">account_circle</span>
                            <?= Format::e($username) ?>
                            <span class="material-symbols-outlined rz-nav__caret" aria-hidden="true">expand_more</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end rz-menu">
                            <a class="rz-menu__item" href="<?= RedirectKey::PROFILE ?>">Profile</a>
                            <form action="<?= RedirectKey::LOGOUT ?>" method="POST">
                                <?= CSRF::field() ?>
                                <button type="submit" class="rz-menu__item">Log out</button>
                            </form>
                        </div>
                    </li>
                <?php else: ?>
                    <li class="rz-nav__item"><a class="rz-nav__link" href="<?= RedirectKey::LOGIN ?>">Log in</a></li>
                    <li class="rz-nav__item"><a class="rz-btn rz-btn--primary rz-btn--sm" href="<?= RedirectKey::SIGNUP ?>">Sign up</a></li>
                <?php endif; ?>

                <li class="rz-nav__item">
                    <a class="rz-nav__link rz-nav__cart" href="<?= RedirectKey::CART ?>" aria-label="Cart, <?= Format::count($cartCount, 'item') ?>">
                        <span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span>
                        <?php if ($cartCount > 0): ?>
                            <span class="rz-nav__badge"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</header>

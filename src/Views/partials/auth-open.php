<?php

declare(strict_types=1);

use Core\View\Format;
use Core\View\View;

/**
 * Opens the auth card: brand, back link, icon, heading and any flashed errors.
 * Pair with auth-close after the form.
 *
 * @var array<string, mixed> $auth  content/auth.php
 * @var array<string, mixed> $page  the "login" or "signup" block
 * @var bool                 $wide  wider card for the sign-up form
 */
?>
<div class="rz-auth__wrap<?= Format::when($wide, ' rz-auth__wrap--wide') ?>">
    <div class="rz-auth__brand rz-reveal">
        <h1>RZA</h1>
        <p><?= Format::e($page['brand_line']) ?></p>
    </div>

    <section class="rz-auth__card rz-reveal" aria-labelledby="auth-title">
        <a class="rz-back" href="<?= Format::e($auth['back']['href']) ?>">
            <span aria-hidden="true">←</span> <?= Format::e($auth['back']['label']) ?>
        </a>

        <span class="rz-auth__icon material-symbols-outlined" aria-hidden="true"><?= Format::e($auth['icon']) ?></span>
        <h2 id="auth-title"><?= Format::e($page['title']) ?></h2>
        <p class="rz-muted rz-auth__lede"><?= Format::e($page['lede']) ?></p>

        <?php View::partial('flash'); ?>

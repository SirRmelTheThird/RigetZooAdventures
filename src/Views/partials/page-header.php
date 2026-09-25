<?php

declare(strict_types=1);

use Core\View\Format;

/**
 * @var array{kicker: string, title: string, lede: string} $header
 */
?>
<header class="rz-page-head rz-reveal">
    <p class="rz-kicker"><?= Format::e($header['kicker']) ?></p>
    <h1><?= Format::e($header['title']) ?></h1>
    <p class="rz-lede"><?= Format::e($header['lede']) ?></p>
</header>

<?php

declare(strict_types=1);

use Core\View\Format;

/**
 * @var string $title
 * @var string $lede
 */
?>
<div class="rz-section-head rz-reveal">
    <h2><?= Format::e($title) ?></h2>
    <p class="rz-lede"><?= Format::e($lede) ?></p>
</div>

<?php

declare(strict_types=1);

use Core\View\Format;
use Core\View\Image;

/**
 * @var string $src
 * @var string $alt
 * @var string $icon
 * @var string $ratio
 * @var bool|null $priority
 */

$priority = $priority ?? false;
$imageSrc = Image::webp($src);
?>

<div class="rz-media rz-media--<?= Format::e($ratio) ?>">
    <span class="rz-media__fallback material-symbols-outlined" aria-hidden="true"><?= Format::e($icon) ?></span>

    <?php if ($src !== ''): ?>
        <img
            class="rz-media__img"
            src="<?= Format::e($imageSrc) ?>"
            alt="<?= Format::e($alt) ?>"
            loading="<?= $priority ? 'eager' : 'lazy' ?>"
            decoding="async"
            fetchpriority="<?= $priority ? 'high' : 'auto' ?>"
            data-hide-on-error
        >
    <?php endif; ?>
</div>
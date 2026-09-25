<?php

declare(strict_types=1);

use Core\View\Format;

/**
 * @var string                                           $icon
 * @var string                                           $title
 * @var string                                           $text
 * @var list<array{label: string, href: string, variant: string}> $actions
 */
?>
<section class="rz-empty rz-reveal">
    <span class="rz-empty__icon material-symbols-outlined" aria-hidden="true"><?= Format::e($icon) ?></span>
    <h2><?= Format::e($title) ?></h2>
    <p><?= Format::e($text) ?></p>
    <div class="rz-actions">
        <?php foreach ($actions as $action): ?>
            <a class="rz-btn rz-btn--<?= Format::e($action['variant']) ?>" href="<?= Format::e($action['href']) ?>"><?= Format::e($action['label']) ?></a>
        <?php endforeach; ?>
    </div>
</section>

<?php

declare(strict_types=1);

use Core\View\Format;
use Core\View\View;

/**
 * Whole-tile link used by the home page bento.
 *
 * @var array{tone: string, title: string, text: string, href: string, media: array<string, string>} $tile
 * @var string $ratio media ratio for this tile
 */
?>
<a class="rz-tile rz-tile--<?= Format::e($tile['tone']) ?> rz-reveal" href="<?= Format::e($tile['href']) ?>">
    <?php View::partial('media', $tile['media'] + ['ratio' => $ratio]); ?>
    <div class="rz-tile__body">
        <h3>
            <?= Format::e($tile['title']) ?>
            <span class="material-symbols-outlined" aria-hidden="true">arrow_outward</span>
        </h3>
        <p><?= Format::e($tile['text']) ?></p>
    </div>
</a>

<?php

declare(strict_types=1);

use Core\View\Format;

/**
 * Dark summary card used by cart, checkout and profile.
 *
 * @var string                                                     $title
 * @var list<array{label: string, value: string, strong?: bool}>   $rows
 * @var string                                                     $actionsHtml pre-rendered, already escaped
 */
?>
<aside class="rz-summary rz-reveal">
    <h2><?= Format::e($title) ?></h2>
    <dl>
        <?php foreach ($rows as $row): ?>
            <div class="rz-summary__row<?= Format::when(array_key_exists('strong', $row), ' rz-summary__row--strong') ?>">
                <dt><?= Format::e($row['label']) ?></dt>
                <dd><?= Format::e($row['value']) ?></dd>
            </div>
        <?php endforeach; ?>
    </dl>
    <?php if ($actionsHtml !== ''): ?>
        <div class="rz-summary__actions"><?= $actionsHtml ?></div>
    <?php endif; ?>
</aside>

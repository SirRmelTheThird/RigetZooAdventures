<?php

declare(strict_types=1);

use Core\View\Format;

/**
 * @var list<string> $items
 */
if ($items === []) {
    return;
}
?>
<ul class="rz-pills">
    <?php foreach ($items as $item): ?>
        <li><?= Format::e($item) ?></li>
    <?php endforeach; ?>
</ul>

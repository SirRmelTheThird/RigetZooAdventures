<?php

declare(strict_types=1);

use Core\View\Format;

?>
<ul class="rz-checks">
    <?php foreach ($items as $item): ?>
        <li>
            <span class="material-symbols-outlined" aria-hidden="true">check</span>
            <?= Format::e($item) ?>
        </li>
    <?php endforeach; ?>
</ul>

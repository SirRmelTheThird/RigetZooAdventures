<?php

declare(strict_types=1);

use Core\View\Format;

?>
<?php if (array_key_exists('children', $item)): ?>
    <li class="rz-nav__item dropdown">
        <button type="button" class="rz-nav__link<?= Format::when($item['active'], ' is-active') ?>" data-bs-toggle="dropdown" aria-expanded="false">
            <?= Format::e($item['label']) ?>
            <span class="material-symbols-outlined rz-nav__caret" aria-hidden="true">expand_more</span>
        </button>
        <div class="dropdown-menu rz-menu">
            <?php foreach ($item['children'] as $child): ?>
                <a class="rz-menu__item<?= Format::when($child['active'], ' is-active') ?>" href="<?= Format::e($child['href']) ?>"<?= Format::when($child['active'], ' aria-current="page"') ?>><?= Format::e($child['label']) ?></a>
            <?php endforeach; ?>
        </div>
    </li>
<?php else: ?>
    <li class="rz-nav__item">
        <a class="rz-nav__link<?= Format::when($item['active'], ' is-active') ?>" href="<?= Format::e($item['href']) ?>"<?= Format::when($item['active'], ' aria-current="page"') ?>><?= Format::e($item['label']) ?></a>
    </li>
<?php endif; ?>

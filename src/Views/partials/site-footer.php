<?php

declare(strict_types=1);

use Core\View\Format;

/**
 * @var array<string, mixed> $site
 * @var array<string, mixed> $terms
 */
?>
<footer class="rz-footer">
    <div class="rz-container rz-footer__inner">
        <div class="rz-footer__brand">
            <p class="rz-footer__name"><?= Format::e($site['name']) ?></p>
            <p><?= Format::e($site['tagline']) ?></p>

            <nav class="rz-social" aria-label="Social links">
                <?php foreach ($site['social'] as $link): ?>
                    <a href="<?= Format::e($link['href']) ?>" aria-label="<?= Format::e($link['label']) ?>">
                        <i class="fa <?= Format::e($link['icon']) ?>" aria-hidden="true"></i>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <details class="rz-terms">
            <summary><?= Format::e($terms['title']) ?></summary>
            <div class="rz-terms__body">
                <?php foreach ($terms['paragraphs'] as $paragraph): ?>
                    <p><?= Format::e($paragraph) ?></p>
                <?php endforeach; ?>
            </div>
        </details>

        <p class="rz-footer__copy">&copy; <?= date('Y') ?> <?= Format::e($site['name']) ?></p>
    </div>
</footer>

<?php

declare(strict_types=1);

use Core\View\Format;

/**
 * @var string       $tone     "success" or "error"
 * @var list<string> $messages
 */
$roles = ['success' => 'status', 'error' => 'alert'];
$icons = ['success' => 'check_circle', 'error' => 'error'];
?>
<div class="rz-alert rz-alert--<?= Format::e($tone) ?>" role="<?= $roles[$tone] ?>">
    <span class="material-symbols-outlined" aria-hidden="true"><?= $icons[$tone] ?></span>
    <?php if (count($messages) === 1): ?>
        <p><?= Format::e($messages[0]) ?></p>
    <?php else: ?>
        <ul>
            <?php foreach ($messages as $message): ?>
                <li><?= Format::e($message) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

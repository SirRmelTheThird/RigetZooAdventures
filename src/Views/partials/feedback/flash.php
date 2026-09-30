<?php

declare(strict_types=1);

use Core\View\View;
use Services\Views\SharedViewData;

/**
 * @var SharedViewData|null $shared
 */

if (!isset($shared) || !$shared instanceof SharedViewData) {
    return;
}

$success = $shared->getSuccessFlash();
$error = $shared->getErrorFlash();
$errors = $shared->getValidationErrors();

if (!$success && !$error && !$errors) {
    return;
}

?>

<div class="rz-container rz-flash" data-flash-message>
    <?php if ($success): ?>
        <?php View::partial('feedback/alert', ['tone' => 'success', 'messages' => [$success]]); ?>
    <?php endif; ?>

    <?php if ($error): ?>
        <?php View::partial('feedback/alert', ['tone' => 'error', 'messages' => [$error]]); ?>
    <?php endif; ?>

    <?php if ($errors): ?>
        <?php View::partial('feedback/alert', ['tone' => 'error', 'messages' => $errors]); ?>
    <?php endif; ?>
</div>
<?php

declare(strict_types=1);

use Core\Constants\SessionKey;
use Core\Session;
use Core\View\View;

// Flash messages are read once, so they are collected in one place.
$success = Session::getFlash(SessionKey::SUCCESS);
$error = Session::getFlash(SessionKey::ERROR);
$errors = Session::getFlash(SessionKey::VALIDATION_ERRORS);

if (!$success && !$error && !$errors) {
    return;
}
?>
<div class="rz-container rz-flash">
    <?php if ($success): ?>
        <?php View::partial('alert', ['tone' => 'success', 'messages' => [$success]]); ?>
    <?php endif; ?>
    <?php if ($error): ?>
        <?php View::partial('alert', ['tone' => 'error', 'messages' => [$error]]); ?>
    <?php endif; ?>
    <?php if ($errors): ?>
        <?php View::partial('alert', ['tone' => 'error', 'messages' => $errors]); ?>
    <?php endif; ?>
</div>

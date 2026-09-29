<?php

declare(strict_types=1);

use Core\Constants\SessionKey;
use Core\Session\Session;
use Core\View\View;

$flash = Session::get(SessionKey::FLASH) ?? [];
$success = $flash[SessionKey::SUCCESS] ?? null;
$error = $flash[SessionKey::ERROR] ?? null;
$errors = $flash[SessionKey::VALIDATION_ERRORS] ?? null;

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
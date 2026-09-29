<?php

declare(strict_types=1);

use Core\Constants\SessionKey;
use Core\Session\Session;
use Core\View\View;

require __DIR__ . '/head.php';

$flash = Session::get(SessionKey::FLASH) ?? [];
?>
<div class="rz-flash" data-flash-message>
    <?php if (!empty($flash[SessionKey::SUCCESS] ?? [])): ?>
        <?php View::partial('feedback/alert', ['tone' => 'success', 'messages' => [$flash[SessionKey::SUCCESS]]]); ?>
    <?php endif; ?>
    <?php if (!empty($flash[SessionKey::ERROR] ?? [])): ?>
        <?php View::partial('feedback/alert', ['tone' => 'error', 'messages' => [$flash[SessionKey::ERROR]]]); ?>
    <?php endif; ?>
    <?php if (!empty($flash[SessionKey::VALIDATION_ERRORS] ?? [])): ?>
        <?php View::partial('feedback/alert', ['tone' => 'error', 'messages' => $flash[SessionKey::VALIDATION_ERRORS]]); ?>
    <?php endif; ?>
</div>

<body class="rz-bare">
    <main class="rz-auth">

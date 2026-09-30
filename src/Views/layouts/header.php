<?php

declare(strict_types=1);

use Core\View\Navigation;
use Core\View\View;
use Services\Views\SharedViewData;

require __DIR__ . '/head.php';

$navItems = Navigation::resolve($site->getNav(), Navigation::pathOf($_SERVER['REQUEST_URI']));

/** @var SharedViewData $shared */
$isLoggedIn = $shared->isLoggedIn();
$username = (string) ($shared->username() ?? '');
$cartCount = $shared->cartCount();
?>
<body>
    <a class="rz-skip" href="#main">Skip to content</a>

    <?php
    View::partial('layout/nav', [
        'site' => $site,
        'navItems' => $navItems,
        'isLoggedIn' => (bool) ($isLoggedIn ?? false),
        'username' => (string) ($username ?? ''),
        'cartCount' => (int) ($cartCount ?? 0),
    ]);
?>

    <main id="main" class="rz-main">
        <?php View::partial('feedback/flash', ['shared' => $shared ?? null]); ?>

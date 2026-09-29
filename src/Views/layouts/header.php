<?php

declare(strict_types=1);

use Core\View\Navigation;
use Core\View\View;
use Core\Session\Session;

require __DIR__ . '/head.php';

$navItems = Navigation::resolve($site->getNav(), Navigation::pathOf($_SERVER['REQUEST_URI']));

$isLoggedIn = Session::isLoggedIn();
$username = (string) (Session::getUsername() ?? '');
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
        <?php View::partial('feedback/flash'); ?>

<?php

declare(strict_types=1);

use Core\Constants\SessionKey;
use Core\Session;
use Core\View\Navigation;
use Core\View\View;

require __DIR__ . '/head.php';

$cart = Session::get(SessionKey::CART, ['items' => []]);

$navItems = Navigation::resolve($site['nav'], Navigation::pathOf($_SERVER['REQUEST_URI']));
?>
<body>
    <a class="rz-skip" href="#main">Skip to content</a>

    <?php
    View::partial('nav', [
        'site' => $site,
        'navItems' => $navItems,
        'isLoggedIn' => Session::isLoggedIn(),
        'username' => (string) Session::getUsername(),
        'cartCount' => count($cart['items']),
    ]);
?>

    <main id="main" class="rz-main">
        <?php View::partial('flash'); ?>

<?php
use Core\Session;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riget Zoo Adventures</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <nav id="nav" class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="brand-mark navbar-brand" href="/" aria-label="Riget Zoo Adventures home">
                <img src="/assets/images/logo.png" alt="" onerror="this.style.display='none'">
                <span>Riget Zoo Adventures</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#primaryNavigation" aria-controls="primaryNavigation" aria-expanded="false" aria-label="Toggle navigation">
                <span class="material-symbols-outlined">menu</span>
            </button>

            <div class="collapse navbar-collapse" id="primaryNavigation">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Bookings
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="/tickets">Tickets</a>
                            <a class="dropdown-item" href="/accommodations">Accommodations</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/attractions">Attractions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/educational">Educational</a>
                    </li>

                    <?php if (Session::isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="material-symbols-outlined" aria-hidden="true">account_circle</span>
                                <?= htmlspecialchars(Session::getUsername()) ?>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="/profile">Profile</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="/logout">
                                    <span class="material-symbols-outlined" aria-hidden="true">logout</span> Logout
                                </a>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/cart" aria-label="Cart">
                                <span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span>
                                <?php
                                $cart = Session::get('cart', ['items' => []]);
                                if (!empty($cart['items'])):
                                ?>
                                    <span class="badge bg-warning"><?= count($cart['items']) ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <span class="nav-link">
                                Points: <?= \Models\RewardPoint::getCustomerBalance(Session::getUserId()) ?>
                            </span>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login">Log In</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/signup">Sign Up</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/cart" aria-label="Cart">
                                <span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="site-main">
        <div class="flash-region" aria-live="polite" aria-atomic="true">
            <div class="container mt-3">
                <?php if ($success = Session::getFlash('success')): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if ($error = Session::getFlash('error')): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($errors = Session::getFlash('errors')): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>

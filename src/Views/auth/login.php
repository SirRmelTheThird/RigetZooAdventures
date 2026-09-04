<?php use Core\CSRF; use Core\Session; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Riget Zoo Adventures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <main class="auth-shell">
        <div class="auth-wrap">
            <div class="auth-brand reveal">
                <h1>RZA</h1>
                <p>Riget Zoo Adventures</p>
            </div>

            <section class="auth-card reveal" aria-labelledby="login-title">
                <a href="/" class="back-link"><span aria-hidden="true">←</span> Back</a>

                <div class="auth-icon" aria-hidden="true">
                    <span class="material-symbols-outlined">passkey</span>
                </div>

                <h2 id="login-title" class="text-center mb-3">Welcome back</h2>
                <p class="text-center mb-4" style="color: var(--earth-700);">Log in to manage bookings, checkout, and reward points.</p>

                <?php if ($error = Session::getFlash('error')): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($errors = Session::getFlash('errors')): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $err): ?>
                            <p class="mb-1"><?= htmlspecialchars($err) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="/login" method="POST">
                    <?= CSRF::field() ?>

                    <div class="form-field">
                        <label for="username">Username</label>
                        <input id="username" type="text" class="form-control" name="username" placeholder="Your username" autocomplete="username" required>
                    </div>

                    <div class="form-field">
                        <label for="password">Password</label>
                        <input id="password" type="password" class="form-control" name="password" placeholder="Your password" autocomplete="current-password" required>
                    </div>

                    <button type="submit" class="submit">Log In</button>

                    <p class="text-center mt-3 mb-0">
                        <a href="/signup" class="auth-link">Create an account</a>
                    </p>
                </form>
            </section>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>

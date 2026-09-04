<?php use Core\CSRF; use Core\Session; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Riget Zoo Adventures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <main class="auth-shell">
        <div class="auth-wrap" style="width: min(100%, 520px);">
            <div class="auth-brand reveal">
                <h1>RZA</h1>
                <p>Create your Riget Zoo Adventures account</p>
            </div>

            <section class="auth-card reveal" aria-labelledby="signup-title">
                <a href="/" class="back-link"><span aria-hidden="true">←</span> Back</a>

                <div class="auth-icon" aria-hidden="true">
                    <span class="material-symbols-outlined">passkey</span>
                </div>

                <h2 id="signup-title" class="text-center mb-3">Start planning</h2>
                <p class="text-center mb-4" style="color: var(--earth-700);">Save bookings, earn points, and move through checkout faster.</p>

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

                <?php $formData = Session::getFlash('form_data') ?? []; ?>

                <form action="/signup" method="POST">
                    <?= CSRF::field() ?>

                    <div class="grid" style="grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px;">
                        <div class="form-field">
                            <label for="first_name">First Name</label>
                            <input id="first_name" type="text" class="form-control" name="first_name" placeholder="First name" value="<?= htmlspecialchars($formData['first_name'] ?? '') ?>" autocomplete="given-name" required>
                        </div>

                        <div class="form-field">
                            <label for="last_name">Last Name</label>
                            <input id="last_name" type="text" class="form-control" name="last_name" placeholder="Last name" value="<?= htmlspecialchars($formData['last_name'] ?? '') ?>" autocomplete="family-name" required>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="username">Username</label>
                        <input id="username" type="text" class="form-control" name="username" placeholder="Choose a username" value="<?= htmlspecialchars($formData['username'] ?? '') ?>" autocomplete="username" required>
                    </div>

                    <div class="form-field">
                        <label for="email">Email</label>
                        <input id="email" type="email" class="form-control" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($formData['email'] ?? '') ?>" autocomplete="email" required>
                    </div>

                    <div class="form-field">
                        <label for="password">Password</label>
                        <input id="password" type="password" class="form-control" name="password" placeholder="Create a password" autocomplete="new-password" required>
                    </div>

                    <div class="form-field">
                        <label for="confirm_password">Confirm Password</label>
                        <input id="confirm_password" type="password" class="form-control" name="confirm_password" placeholder="Repeat your password" autocomplete="new-password" required>
                    </div>

                    <button type="submit" class="submit">Sign Up</button>

                    <p class="text-center mt-3 mb-2">
                        <a href="/login" class="auth-link">Already have an account?</a>
                    </p>

                    <p class="text-center mb-0" style="font-size: 0.86rem; color: var(--earth-500);">
                        By creating an account, you agree to our Terms and Privacy Policy.
                    </p>
                </form>
            </section>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>

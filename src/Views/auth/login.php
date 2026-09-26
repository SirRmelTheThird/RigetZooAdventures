<?php

declare(strict_types=1);

use App\Models\AuthContentInterface;
use Core\Constants\RedirectKey;
use Core\CSRF;
use Core\View\Format;
use Core\View\View;

$pageTitle = 'Log in';
require __DIR__ . '/../layouts/bare-header.php';

$auth = View::content(AuthContentInterface::class);
$page = $auth->getLogin();

View::partial('auth-open', ['auth' => $auth, 'page' => $page, 'wide' => false]);
?>
        <form action="<?= RedirectKey::LOGIN ?>" method="POST">
            <?= CSRF::field() ?>
            <?php
            View::partial('form-field', ['id' => 'username', 'name' => 'username', 'label' => 'Username', 'type' => 'text', 'attrs' => ['placeholder' => 'Your username', 'autocomplete' => 'username']]);
            View::partial('form-field', ['id' => 'password', 'name' => 'password', 'label' => 'Password', 'type' => 'password', 'attrs' => ['placeholder' => 'Your password', 'autocomplete' => 'current-password']]);
            ?>
            <button type="submit" class="rz-btn rz-btn--primary rz-btn--block"><?= Format::e($page['submit_label']) ?></button>
            <p class="rz-auth__switch"><a href="<?= Format::e($page['switch']['href']) ?>"><?= Format::e($page['switch']['label']) ?></a></p>
        </form>
<?php
View::partial('auth-close');
require __DIR__ . '/../layouts/bare-footer.php';
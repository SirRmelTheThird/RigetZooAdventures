<?php

declare(strict_types=1);

use App\Models\AuthContentInterface;
use Core\Constants\RedirectKey;
use Core\Constants\SessionKey;
use Core\CSRF;
use Core\Session;
use Core\View\Format;
use Core\View\OldInput;
use Core\View\View;

$pageTitle = 'Sign up';
require __DIR__ . '/../layouts/bare-header.php';

$auth = View::content(AuthContentInterface::class);
$page = $auth->getSignup();
$old = OldInput::fromFlash(Session::getFlash(SessionKey::FORM_DATA));

View::partial('auth-open', ['auth' => $auth, 'page' => $page, 'wide' => true]);
?>
        <form action="<?= RedirectKey::SIGNUP ?>" method="POST">
            <?= CSRF::field() ?>

            <div class="rz-field-row">
                <?php
                View::partial('form-field', ['id' => 'first_name', 'name' => 'first_name', 'label' => 'First name', 'type' => 'text', 'attrs' => ['placeholder' => 'First name', 'autocomplete' => 'given-name', 'value' => $old->get('first_name')]]);
                View::partial('form-field', ['id' => 'last_name', 'name' => 'last_name', 'label' => 'Last name', 'type' => 'text', 'attrs' => ['placeholder' => 'Last name', 'autocomplete' => 'family-name', 'value' => $old->get('last_name')]]);
                ?>
            </div>

            <?php
            View::partial('form-field', ['id' => 'username', 'name' => 'username', 'label' => 'Username', 'type' => 'text', 'attrs' => ['placeholder' => 'Choose a username', 'autocomplete' => 'username', 'value' => $old->get('username')]]);
            View::partial('form-field', ['id' => 'email', 'name' => 'email', 'label' => 'Email', 'type' => 'email', 'attrs' => ['placeholder' => 'you@example.com', 'autocomplete' => 'email', 'value' => $old->get('email')]]);
            View::partial('form-field', ['id' => 'password', 'name' => 'password', 'label' => 'Password', 'type' => 'password', 'attrs' => ['placeholder' => 'Create a password', 'autocomplete' => 'new-password']]);
            View::partial('form-field', ['id' => 'confirm_password', 'name' => 'confirm_password', 'label' => 'Confirm password', 'type' => 'password', 'attrs' => ['placeholder' => 'Repeat your password', 'autocomplete' => 'new-password']]);
            ?>

            <button type="submit" class="rz-btn rz-btn--primary rz-btn--block"><?= Format::e($page['submit_label']) ?></button>
            <p class="rz-auth__switch"><a href="<?= Format::e($page['switch']['href']) ?>"><?= Format::e($page['switch']['label']) ?></a></p>
            <p class="rz-auth__legal"><?= Format::e($page['legal']) ?></p>
        </form>
<?php
View::partial('auth-close');
require __DIR__ . '/../layouts/bare-footer.php';
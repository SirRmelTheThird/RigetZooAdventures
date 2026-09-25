<?php

declare(strict_types=1);

use Core\Constants\RedirectKey;

return [
    'back' => ['label' => 'Back', 'href' => RedirectKey::HOME],
    'icon' => 'passkey',
    'login' => [
        'page_title' => 'Log in',
        'brand_line' => 'Riget Zoo Adventures',
        'title' => 'Welcome back',
        'lede' => 'Log in to manage bookings, checkout, and reward points.',
        'submit_label' => 'Log in',
        'switch' => ['label' => 'Create an account', 'href' => RedirectKey::SIGNUP],
    ],
    'signup' => [
        'page_title' => 'Sign up',
        'brand_line' => 'Create your Riget Zoo Adventures account',
        'title' => 'Start planning',
        'lede' => 'Save bookings, earn points, and move through checkout faster.',
        'submit_label' => 'Sign up',
        'switch' => ['label' => 'Already have an account?', 'href' => RedirectKey::LOGIN],
        'legal' => 'By creating an account, you agree to our Terms and Privacy Policy.',
    ],
];

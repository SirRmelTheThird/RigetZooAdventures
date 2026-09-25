<?php

declare(strict_types=1);

use Core\Constants\RedirectKey;

return [
    'home' => ['label' => 'Return to homepage', 'href' => RedirectKey::HOME],
    'not_found' => [
        'code' => '404',
        'page_title' => 'Page not found',
        'title' => 'Page not found',
        'message' => 'The page you are looking for does not exist.',
    ],
    'server_error' => [
        'code' => '500',
        'page_title' => 'Something went wrong',
        'title' => 'Something went wrong',
        'message' => 'Something failed on our end and we are working on it. Please try again later or contact support if the problem persists.',
    ],
];

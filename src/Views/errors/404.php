<?php

declare(strict_types=1);

use Core\View\View;

View::bind(dirname(__DIR__));

$content = View::content('errors');
$error = $content['not_found'];

if (isset($message)) {
    $error['message'] = $message;
}

$pageTitle = $error['page_title'];
require __DIR__ . '/../layouts/bare-header.php';

View::partial('error-page', ['error' => $error, 'home' => $content['home']]);

require __DIR__ . '/../layouts/bare-footer.php';

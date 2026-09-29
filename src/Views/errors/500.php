<?php

declare(strict_types=1);

use Core\View\View;
use Contracts\ErrorsContentInterface;

View::bind(dirname(__DIR__));

$content = View::content(ErrorsContentInterface::class);
$error = $content->getServerError();

$pageTitle = $error['page_title'];
require __DIR__ . '/../layouts/bare-header.php';

View::partial('feedback/error-page', ['error' => $error, 'home' => $content->getHome()]);

require __DIR__ . '/../layouts/bare-footer.php';

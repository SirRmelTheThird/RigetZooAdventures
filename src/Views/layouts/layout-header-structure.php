<?php

declare(strict_types=1);

use Core\View\Format;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Format::e($documentTitle ?? 'Site') ?></title>
</head>
<body>
<a class="rz-skip" href="#main">Skip to content</a>

<?php

declare(strict_types=1);

use Core\View\Format;
use Core\View\View;

View::bind(dirname(__DIR__));

$site = View::content('site');

$documentTitle = $site['name'];
if (isset($pageTitle)) {
    $documentTitle = $pageTitle . ' | ' . $site['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= Format::e($site['description']) ?>">
    <meta name="theme-color" content="#12240f">
    <link rel="icon" type="image/png" sizes="64x64" href="/assets/images/logo/rza-logo.png">
    <title><?= Format::e($documentTitle) ?></title>
    <script>document.documentElement.classList.add('js');</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500..800&family=Geist:wght@400..700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>

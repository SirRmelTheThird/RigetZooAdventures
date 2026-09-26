<?php
/**
 * Layout header structure (AUD-ARCH-004 split)
 * Separated from presentation (nav, session, cart) to allow reuse without full chrome.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $documentTitle ?? 'Site' ?></title>
</head>
<body>
<a class="rz-skip" href="#main">Skip to content</a>

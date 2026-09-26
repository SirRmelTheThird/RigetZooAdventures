<?php

declare(strict_types=1);

use Core\View\View;

$pageTitle = 'Stays';
require __DIR__ . '/../layouts/header.php';

$content = View::content('stays');
$items = $content['accommodations'];
?>

<div class="rz-container rz-page">
    <?php View::partial('page-header', ['header' => $content['header']]); ?>

    <?php if (count($items) === 0): ?>
        <?php View::partial('empty-state', $content['empty'] + ['icon' => 'cabin', 'actions' => []]); ?>
    <?php endif; ?>

    <div class="rz-stays">
        <?php foreach ($items as $item): ?>
            <?php View::partial('stay-card', [
                'accommodation' => $item['accommodation'],
                'unavailable' => $item['unavailable'],
                'copy' => $content['card'],
            ]); ?>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

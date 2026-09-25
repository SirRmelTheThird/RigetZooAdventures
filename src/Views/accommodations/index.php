<?php

declare(strict_types=1);

use Core\View\View;

$pageTitle = 'Stays';
require __DIR__ . '/../layouts/header.php';

$content = View::content('stays');
?>

<div class="rz-container rz-page">
    <?php View::partial('page-header', ['header' => $content['header']]); ?>

    <?php if (count($accommodations) === 0): ?>
        <?php View::partial('empty-state', $content['empty'] + ['icon' => 'cabin', 'actions' => []]); ?>
    <?php endif; ?>

    <div class="rz-stays">
        <?php foreach ($accommodations as $accommodation): ?>
            <?php View::partial('stay-card', ['accommodation' => $accommodation, 'copy' => $content['card']]); ?>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

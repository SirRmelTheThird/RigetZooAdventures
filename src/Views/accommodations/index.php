<?php

declare(strict_types=1);

use Core\View\View;
use Contracts\StaysContentInterface;

/**
 * @var array<int, array{accommodation: object, unavailable: array}> $items
 */

$pageTitle = 'Stays';
require __DIR__ . '/../layouts/header.php';

$content = View::content(StaysContentInterface::class);
?>

<div class="rz-container rz-page">
    <?php View::partial('layout/page-header', ['header' => $content->getHeader()]); ?>

    <?php if (count($items) === 0): ?>
        <?php View::partial('feedback/empty-state', $content->getEmpty() + ['icon' => 'cabin', 'actions' => []]); ?>
    <?php endif; ?>

    <div class="rz-stays">
        <?php foreach ($items as $item): ?>
            <?php View::partial('catalog/stay-card', [
                'accommodation' => $item['accommodation'],
                'unavailable' => $item['unavailable'],
                'copy' => $content->getCard(),
            ]); ?>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
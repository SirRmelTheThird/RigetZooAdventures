<?php

declare(strict_types=1);

use Core\View\Format;
use Core\View\View;
use Contracts\AttractionsContentInterface;

$pageTitle = 'Attractions';
require __DIR__ . '/layouts/header.php';

$content = View::content(AttractionsContentInterface::class);

[$lead, $shop, $dining] = $content->getFeatures();
$closing = $content->getClosing();
?>

<div class="rz-container rz-page">
    <?php View::partial('layout/page-header', ['header' => $content->getHeader()]); ?>

    <div class="rz-attractions">
        <article class="rz-feature rz-feature--lead rz-reveal">
            <?php View::partial('layout/media', $lead['media'] + ['ratio' => 'fill']); ?>
            <div class="rz-feature__body">
                <h2><?= Format::e($lead['title']) ?></h2>
                <p class="rz-muted"><?= Format::e($lead['text']) ?></p>
                <?php View::partial('forms/pills', ['items' => $lead['pills']]); ?>
            </div>
        </article>

        <?php foreach ([$shop, $dining] as $feature): ?>
            <article class="rz-feature rz-reveal">
                <?php View::partial('layout/media', $feature['media'] + ['ratio' => '16x10']); ?>
                <div class="rz-feature__body">
                    <h2><?= Format::e($feature['title']) ?></h2>
                    <p class="rz-muted"><?= Format::e($feature['text']) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <section class="rz-cta-band rz-reveal">
        <div>
            <h2><?= Format::e($closing['title']) ?></h2>
            <p><?= Format::e($closing['text']) ?></p>
        </div>
        <a class="rz-btn rz-btn--primary" href="<?= Format::e($closing['cta']['href']) ?>"><?= Format::e($closing['cta']['label']) ?></a>
    </section>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>

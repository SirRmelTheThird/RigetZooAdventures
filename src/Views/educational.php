<?php

declare(strict_types=1);

use Core\View\Format;
use Core\View\View;

$pageTitle = 'Educational visits';
require __DIR__ . '/layouts/header.php';

$content = View::content('educational');
$feature = $content['feature'];
?>

<div class="rz-container rz-page">
    <?php View::partial('page-header', ['header' => $content['header']]); ?>

    <div class="rz-learn">
        <section class="rz-learn__feature rz-reveal">
            <h2><?= Format::e($feature['title']) ?></h2>
            <p><?= Format::e($feature['text']) ?></p>
            <?php View::partial('pills', ['items' => $feature['pills']]); ?>
            <a class="rz-btn rz-btn--primary" href="<?= Format::e($feature['cta']['href']) ?>"><?= Format::e($feature['cta']['label']) ?></a>
        </section>

        <div class="rz-learn__topics">
            <?php foreach ($content['topics'] as $topic): ?>
                <article class="rz-topic rz-reveal">
                    <span class="rz-topic__icon material-symbols-outlined" aria-hidden="true"><?= Format::e($topic['icon']) ?></span>
                    <div>
                        <h3><?= Format::e($topic['title']) ?></h3>
                        <p class="rz-muted"><?= Format::e($topic['text']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>

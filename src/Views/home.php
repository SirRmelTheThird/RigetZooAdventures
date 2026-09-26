<?php

declare(strict_types=1);

use App\Models\HomeContentInterface;
use Core\View\Format;
use Core\View\View;

$pageTitle = 'Home';
require __DIR__ . '/layouts/header.php';

$home = View::content(HomeContentInterface::class);
$hero = $home->getHero();
$visit = $home->getVisit();
[$featuredTile, $stayTile, $attractionTile] = $visit['tiles'];
?>

<section class="rz-container rz-hero">
    <div class="rz-hero__copy rz-reveal">
        <p class="rz-kicker"><?= Format::e($hero['kicker']) ?></p>
        <h1><?= Format::e($hero['title']) ?></h1>
        <p class="rz-lede"><?= Format::e($hero['lede']) ?></p>
        <div class="rz-actions">
            <a class="rz-btn rz-btn--primary" href="<?= Format::e($hero['primary']['href']) ?>"><?= Format::e($hero['primary']['label']) ?></a>
            <a class="rz-btn rz-btn--secondary" href="<?= Format::e($hero['secondary']['href']) ?>"><?= Format::e($hero['secondary']['label']) ?></a>
        </div>
    </div>

    <div class="rz-hero__media rz-reveal">
        <?php View::partial('media', $hero['media'] + ['ratio' => 'fill', 'priority' => true]); ?>
    </div>
</section>

<section class="rz-container rz-facts rz-reveal" aria-label="At a glance">
    <?php foreach ($home->getFacts() as $fact): ?>
        <div class="rz-fact">
            <span class="material-symbols-outlined" aria-hidden="true"><?= Format::e($fact['icon']) ?></span>
            <div>
                <strong><?= Format::e($fact['title']) ?></strong>
                <span><?= Format::e($fact['text']) ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<section class="rz-container rz-section">
    <?php View::partial('section-head', ['title' => $visit['title'], 'lede' => $visit['lede']]); ?>

    <div class="rz-bento">
        <?php View::partial('link-tile', ['tile' => $featuredTile, 'ratio' => '16x10']); ?>
        <?php View::partial('link-tile', ['tile' => $stayTile, 'ratio' => 'fill']); ?>
        <?php View::partial('link-tile', ['tile' => $attractionTile, 'ratio' => 'fill']); ?>
    </div>
</section>

<?php require __DIR__ . '/layouts/footer.php'; ?>

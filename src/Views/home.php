<?php require __DIR__ . '/layouts/header.php'; ?>

<section class="home-hero">
    <div class="hero-copy reveal">
        <p class="page-kicker">Plan your visit</p>
        <h1>Wild days, restful nights.</h1>
        <p>Book zoo tickets, safari experiences, and park stays with one clear path from planning to checkout.</p>
        <div class="hero-actions">
            <a class="btn-primary-rza" href="/tickets">Book Tickets</a>
            <a class="btn-secondary-rza" href="/accommodations">View Stays</a>
        </div>
    </div>

    <div class="media-frame hero-media reveal">
        <img src="/assets/images/deer.jpg" alt="Deer at Riget Zoo Adventures" onerror="this.closest('.media-frame').classList.add('is-missing'); this.remove();">
        <div class="media-fallback">Riget Zoo Adventures</div>
    </div>
</section>

<section class="site-section">
    <div class="page-header reveal">
        <h2 class="page-title">A full visit in one place.</h2>
        <p class="page-subtitle">Choose admission, add a stay inside the park, and keep your day focused on the animals instead of the admin.</p>
    </div>

    <div class="grid">
        <article class="destination-card content-card reveal">
            <a class="media-frame" href="/accommodations" aria-label="View accommodations">
                <img src="/assets/images/hotel.jpg" alt="Safari accommodation at Riget Zoo Adventures" onerror="this.closest('.media-frame').classList.add('is-missing'); this.remove();">
                <span class="media-fallback">Stay inside the park</span>
            </a>
            <div class="destination-card-body">
                <h3>Accommodations</h3>
                <p>Wake near the wildlife with lodges, hotels, and glamping stays built around the park experience.</p>
                <a class="btn-secondary-rza" href="/accommodations">Explore Stays</a>
            </div>
        </article>

        <article class="destination-card content-card reveal">
            <a class="media-frame" href="/attractions" aria-label="View attractions">
                <img src="/assets/images/train.jpg" alt="Zoo train and attraction route" onerror="this.closest('.media-frame').classList.add('is-missing'); this.remove();">
                <span class="media-fallback">Park attractions</span>
            </a>
            <div class="destination-card-body">
                <h3>Attractions</h3>
                <p>Move through habitats, animal encounters, guided experiences, restaurants, and family facilities.</p>
                <a class="btn-secondary-rza" href="/attractions">See Attractions</a>
            </div>
        </article>

        <article class="destination-card content-card reveal">
            <a class="media-frame" href="/tickets" aria-label="View tickets">
                <img src="/assets/images/ticket.jpg" alt="Riget Zoo Adventures admission tickets" onerror="this.closest('.media-frame').classList.add('is-missing'); this.remove();">
                <span class="media-fallback">Admission passes</span>
            </a>
            <div class="destination-card-body">
                <h3>Tickets</h3>
                <p>Pick Standard for a full day of discovery or Premium for added safari access and guided experiences.</p>
                <a class="btn-primary-rza" href="/tickets">Choose Tickets</a>
            </div>
        </article>
    </div>
</section>

<?php require __DIR__ . '/layouts/footer.php'; ?>

<?php require __DIR__ . '/layouts/header.php'; ?>

<div class="page-content">
    <header class="page-header reveal">
        <p class="page-kicker">Inside the park</p>
        <h1>Attractions and Facilities</h1>
        <p class="page-subtitle">Explore animal habitats, visitor routes, restaurants, and the everyday stops that make the park easy to enjoy.</p>
    </header>

    <div class="editorial-list">
        <article class="editorial-feature content-card reveal">
            <div class="media-frame">
                <img src="/assets/images/train.jpg" alt="Zoo train route at Riget Zoo Adventures" onerror="this.closest('.media-frame').classList.add('is-missing'); this.remove();">
                <span class="media-fallback">Attractions</span>
            </div>
            <div>
                <h2>Wildlife routes</h2>
                <p>Move between animal exhibits, immersive habitats, keeper encounters, children’s play areas, shows, and guided transport routes through the sanctuary.</p>
                <div class="stat-row">
                    <span class="stat-pill">Animal habitats</span>
                    <span class="stat-pill">Zoo train</span>
                    <span class="stat-pill">Family facilities</span>
                </div>
            </div>
        </article>

        <article class="editorial-feature content-card reveal">
            <div class="media-frame">
                <img src="/assets/images/giftshop.jpg" alt="Gift shop at Riget Zoo Adventures" onerror="this.closest('.media-frame').classList.add('is-missing'); this.remove();">
                <span class="media-fallback">Gift Shop</span>
            </div>
            <div>
                <h2>Gift shop</h2>
                <p>Pick up safari clothing, plush animals, handmade keepsakes, and wildlife-inspired gifts after your day in the park.</p>
                <a class="btn-secondary-rza" href="/tickets">Plan a Visit</a>
            </div>
        </article>

        <article class="editorial-feature content-card reveal">
            <div class="media-frame">
                <img src="/assets/images/res.jpg" alt="Restaurant seating at Riget Zoo Adventures" onerror="this.closest('.media-frame').classList.add('is-missing'); this.remove();">
                <span class="media-fallback">Restaurants</span>
            </div>
            <div>
                <h2>Restaurants</h2>
                <p>Take a proper break with casual meals, scenic views, and food options placed close to the main visitor routes.</p>
                <a class="btn-primary-rza" href="/tickets">Book Tickets</a>
            </div>
        </article>
    </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>

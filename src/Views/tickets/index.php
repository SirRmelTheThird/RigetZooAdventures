<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="page-content">
    <header class="page-header reveal">
        <p class="page-kicker">Admission</p>
        <h1>Select Your Tickets</h1>
        <p class="page-subtitle">Choose a simple day pass or unlock the full safari route with premium access.</p>
    </header>

    <div class="ticket-choice-grid">
        <article class="ticket-choice content-card reveal">
            <div>
                <h2>Standard Ticket</h2>
                <p>Full-day entry to the zoo, animal habitats, visitor paths, restaurants, and family facilities.</p>
            </div>
            <div class="ticket-meta">
                <span class="stat-pill">Adult $25</span>
                <span class="stat-pill">Child $20</span>
            </div>
            <a class="btn-secondary-rza" href="/tickets/standard">View Standard</a>
        </article>

        <article class="ticket-choice content-card featured reveal">
            <div>
                <h2>Premium Ticket</h2>
                <p>Includes premium safari experiences, guided access, and the widest route through the park.</p>
            </div>
            <div class="ticket-meta">
                <span class="stat-pill">Adult $50</span>
                <span class="stat-pill">Child $40</span>
            </div>
            <a class="btn-primary-rza" href="/tickets/premium">View Premium</a>
        </article>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

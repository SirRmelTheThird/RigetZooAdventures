<?php

declare(strict_types=1);

use Core\View\View;
use Contracts\SiteContentInterface;
use Contracts\TermsContentInterface;

?>
    </main>

    <?php
    View::partial('layout/site-footer', [
        'site' => $site = View::content(SiteContentInterface::class),
        'terms' => View::content(TermsContentInterface::class),
    ]);
?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>

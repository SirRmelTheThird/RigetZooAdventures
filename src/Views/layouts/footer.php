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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" crossorigin="anonymous"></script>
    <script type="module" src="/assets/js/main.js"></script>
</body>
</html>

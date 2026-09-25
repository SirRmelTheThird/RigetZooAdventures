<?php

declare(strict_types=1);

use Core\View\View;

?>
    </main>

    <?php
    View::partial('site-footer', [
        'site' => View::content('site'),
        'terms' => View::content('terms'),
    ]);
?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>

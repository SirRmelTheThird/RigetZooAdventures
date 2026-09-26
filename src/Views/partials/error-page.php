<?php

declare(strict_types=1);

use Core\View\Format;

?>
<section class="rz-auth__card rz-error rz-reveal" aria-labelledby="error-title">
    <p class="rz-error__code"><?= Format::e($error['code']) ?></p>
    <h1 id="error-title"><?= Format::e($error['title']) ?></h1>
    <p class="rz-muted"><?= Format::e($error['message']) ?></p>
    <a class="rz-btn rz-btn--primary" href="<?= Format::e($home['href']) ?>"><?= Format::e($home['label']) ?></a>
</section>

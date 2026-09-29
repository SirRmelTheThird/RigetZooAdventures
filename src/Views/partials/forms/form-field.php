<?php

declare(strict_types=1);

use Core\View\Format;

/**
 * @var string $id
 * @var string $label
 * @var string $type
 * @var string $name
 * @var array<string, string> $attrs
 */

?>
<div class="rz-field">
    <label for="<?= Format::e($id) ?>"><?= Format::e($label) ?></label>
    <input id="<?= Format::e($id) ?>" type="<?= Format::e($type) ?>" name="<?= Format::e($name) ?>" required<?php foreach ($attrs as $attribute => $value): ?> <?= Format::e($attribute) ?>="<?= Format::e($value) ?>"<?php endforeach; ?>>
</div>

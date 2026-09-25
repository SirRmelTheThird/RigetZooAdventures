<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

if (!class_exists(\Illuminate\Database\Eloquent\Model::class)) {
    require __DIR__ . '/stubs/Model.php';
}

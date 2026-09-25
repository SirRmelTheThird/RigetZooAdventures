<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $path = dirname(__DIR__) . '/src/' . str_replace('\\', '/', $class) . '.php';

    if (is_file($path)) {
        require $path;
    }
});

if (!class_exists(\Illuminate\Database\Eloquent\Model::class, false)) {
    require __DIR__ . '/stubs/Model.php';
}

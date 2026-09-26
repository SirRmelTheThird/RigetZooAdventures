<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

\Config\Config::load();

$capsule = new Capsule();

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => \Config\Config::get('DB_HOST', $_ENV['DB_HOST']),
    'database' => \Config\Config::get('DB_NAME', $_ENV['DB_NAME']),
    'username' => \Config\Config::get('DB_USER', $_ENV['DB_USER']),
    'password' => \Config\Config::get('DB_PASSWORD', $_ENV['DB_PASSWORD']),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

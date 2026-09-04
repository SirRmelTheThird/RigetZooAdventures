
<?php

use Illuminate\Database\Capsule\Manager as Capsule;

\Config\Config::load();

$capsule = new Capsule;

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => \Config\Config::get('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost'),
    'database' => \Config\Config::get('DB_NAME', $_ENV['DB_NAME'] ?? 'riget_zoo_adventures'),
    'username' => \Config\Config::get('DB_USER', $_ENV['DB_USER'] ?? 'root'),
    'password' => \Config\Config::get('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();
<?php

declare(strict_types=1);

use Config\Config;
use Config\Settings\DbSettings;
use Illuminate\Database\Capsule\Manager as Capsule;

Config::load();

$db = DbSettings::fromEnv();

$capsule = new Capsule();

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => $db->host,
    'port' => $db->port,
    'database' => $db->database,
    'username' => $db->username,
    'password' => $db->password,
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$migrations = [
    'CreateCustomersTable',
    'CreateTicketsTable',
    'CreateAccommodationsTable',
    'CreateOrdersTable',
    'CreateOrderItemsTable',
    'CreateRewardPointsTable',
];

foreach ($migrations as $migration) {
    $class = "Database\\Migrations\\{$migration}";
    require_once __DIR__ . "/migrations/{$migration}.php";

    $instance = new $class();
    echo "Migrating: {$migration}\n";
    $instance->up();
}
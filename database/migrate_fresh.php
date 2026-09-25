<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$tables = ['reward_points', 'order_items', 'orders', 'accommodations', 'tickets', 'customers'];

foreach ($tables as $table) {
    Capsule::schema()->dropIfExists($table);
    echo "Dropped: {$table}\n";
}

require __DIR__ . '/migrate.php';

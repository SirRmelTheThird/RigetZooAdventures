<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$tables = [
    'reward_points',
    'accommodation_availabilities',
    'order_items',
    'orders',
    'processed_webhook_events',
    'accommodations',
    'tickets',
    'customers',
];

foreach ($tables as $table) {
    Capsule::schema()->dropIfExists($table);

    echo "Dropped: {$table}\n";
}

require __DIR__ . '/migrate.php';

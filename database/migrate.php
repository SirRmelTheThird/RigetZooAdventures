<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$migrations = [
    'CreateCustomersTable',
    'CreateTicketsTable',
    'CreateAccommodationsTable',
    'CreateOrdersTable',
    'CreateOrderItemsTable',
    'CreateAccommodationAvailabilitiesTable',
    'CreateRewardPointsTable',
    'CreateProcessedWebhookEventsTable',
    'AddStripeCustomerIdToCustomersTable',
];

foreach ($migrations as $migration) {
    $class = "Database\\Migrations\\{$migration}";
    require_once __DIR__ . "/migrations/{$migration}.php";

    $instance = new $class();
    if (str_starts_with($migration, 'Create')) {
        $table = match ($migration) {
            'CreateCustomersTable' => 'customers',
            'CreateTicketsTable' => 'tickets',
            'CreateAccommodationsTable' => 'accommodations',
            'CreateOrdersTable' => 'orders',
            'CreateOrderItemsTable' => 'order_items',
            'CreateAccommodationAvailabilitiesTable' => 'accommodation_availabilities',
            'CreateRewardPointsTable' => 'reward_points',
            'CreateProcessedWebhookEventsTable' => 'processed_webhook_events',
            default => null,
        };

        if ($table !== null && Capsule::schema()->hasTable($table)) {
            echo "Skipping: {$migration} ({$table} already exists)\n";
            continue;
        }
    }

    echo "Migrating: {$migration}\n";
    $instance->up();
}

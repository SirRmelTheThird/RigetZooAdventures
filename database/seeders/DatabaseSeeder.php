<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Support\AvailabilityGenerator;

final class DatabaseSeeder
{
    public function __construct(
        private readonly AvailabilityGenerator $availabilityGenerator,
        private readonly CustomerSeeder $customerSeeder,
        private readonly TicketSeeder $ticketSeeder,
        private readonly AccommodationSeeder $accommodationSeeder,
    ) {
    }

    /**
     * Builds the seeder with all its dependencies wired up.
     * Call this from the CLI instead of `new DatabaseSeeder(...)`.
     */
    public static function create(): self
    {
        return new self(
            new AvailabilityGenerator(__DIR__ . '/JSON/accommodations.json'),
            new CustomerSeeder(),
            new TicketSeeder(),
            new AccommodationSeeder(),
        );
    }

    public function run(): void
    {
        $this->customerSeeder->run();
        $this->ticketSeeder->run();

        $this->availabilityGenerator->generate();

        $this->accommodationSeeder->run();

        $this->printSummary();
    }

    private function printSummary(): void
    {
        echo "\n✅ Database seeded successfully!\n";

        echo "\nTest Accounts:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Email: john@example.com | Password: password123\n";
        echo "Email: jane@example.com | Password: password123\n";
        echo "Email: admin@riget.com  | Password: admin123\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    }
}
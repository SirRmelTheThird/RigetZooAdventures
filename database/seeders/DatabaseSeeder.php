<?php

namespace Database\Seeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Models\Auth\Customer;
use Models\Tickets\Ticket;
use Models\Accommodations\Accommodation;
use Models\Accommodations\AccommodationAvailability;

class DatabaseSeeder
{
    public function run()
    {
        $this->seedCustomers();
        $this->seedTickets();
        $this->seedAccommodations();

        echo "\n✅ Database seeded successfully!\n";
        echo "\nTest Accounts:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Email: john@example.com | Password: password123\n";
        echo "Email: jane@example.com | Password: password123\n";
        echo "Email: admin@riget.com  | Password: admin123\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    }

    private function seedCustomers()
    {
        $jsonPath = __DIR__ . '/users.json';
        $users = json_decode(file_get_contents($jsonPath), true);

        foreach ($users as $userData) {
            Customer::create([
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'id' => (string) uniqid(), 'password' => $userData['password']
            ]);
        }

        echo "Seeded: customers (" . count($users) . " accounts)\n";
    }

    private function seedTickets()
    {
        $jsonPath = __DIR__ . '/tickets.json';
        $tickets = json_decode(file_get_contents($jsonPath), true);

        foreach ($tickets as $ticket) {
            Ticket::create(array_merge($ticket, ["id" => (string) uniqid()]));
        }

        echo "Seeded: tickets (" . count($tickets) . " types)\n";
    }

    private function seedAccommodations()
    {
        $jsonPath = __DIR__ . '/accommodations.json';
        $accommodations = json_decode(file_get_contents($jsonPath), true);
        $availabilityCount = 0;

        foreach ($accommodations as $raw) {
            $windows = $raw['available_windows'] ?? [];
            $availableFrom = $raw['available_from'] ?? null;
            $availableUntil = $raw['available_until'] ?? null;

            unset($raw['available_windows']);

            $model = Accommodation::create(array_merge($raw, ['id' => (string) uniqid()]));

            $unavailable = $this->buildUnavailableRanges($windows, $availableFrom, $availableUntil);
            foreach ($unavailable as $range) {
                AccommodationAvailability::create([
                    'id' => (string) uniqid(),
                    'accommodation_id' => (string) $model->id,
                    'start_date' => $range['start_date'],
                    'end_date' => $range['end_date'],
                    'reason' => $range['reason'],
                    'is_available' => false,
                ]);
                $availabilityCount++;
            }
        }

        echo "Seeded: accommodations (" . count($accommodations) . " options, $availabilityCount unavailable ranges)\n";
    }

    /**
     * Convert available windows and operating season dates into unavailable gap ranges.
     *
     * @param array<int, array{start: string, end: string}> $windows
     * @param string|null $availableFrom
     * @param string|null $availableUntil
     * @return array<int, array{start_date: string, end_date: string, reason: string}>
     */
    private function buildUnavailableRanges(
        array $windows,
        ?string $availableFrom,
        ?string $availableUntil
    ): array {
        $unavailable = [];

        // Gap before the season opens
        if ($availableFrom !== null) {
            $yearStart = substr($availableFrom, 0, 4) . '-01-01';
            if ($yearStart < $availableFrom) {
                $unavailable[] = [
                    'start_date' => $yearStart,
                    'end_date' => $availableFrom,
                    'reason' => 'season',
                ];
            }
        }

        // Gaps between consecutive available windows
        if ($windows !== []) {
            usort($windows, fn ($a, $b) => $a['start'] <=> $b['start']);
            for ($i = 0; $i < count($windows) - 1; $i++) {
                $gapStart = $windows[$i]['end'];
                $gapEnd = $windows[$i + 1]['start'];
                if ($gapStart < $gapEnd) {
                    $unavailable[] = [
                        'start_date' => $gapStart,
                        'end_date' => $gapEnd,
                        'reason' => 'maintenance',
                    ];
                }
            }
        }

        // Gap after the season closes
        if ($availableUntil !== null) {
            $yearEnd = substr($availableUntil, 0, 4) . '-12-31';
            if ($availableUntil < $yearEnd) {
                $unavailable[] = [
                    'start_date' => $availableUntil,
                    'end_date' => $yearEnd,
                    'reason' => 'season',
                ];
            }
        }

        return $unavailable;
    }
}

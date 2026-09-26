<?php

namespace Database\Seeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Models\Customer;
use Models\Ticket;
use Models\Accommodation;

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

        foreach ($accommodations as $accommodation) {
            Accommodation::create(array_merge($accommodation, ["id" => (string) uniqid()]));
        }

        echo "Seeded: accommodations (" . count($accommodations) . " options)\n";
    }
}

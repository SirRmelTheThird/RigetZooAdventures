<?php

namespace Database\Seeders;

use Database\Seeders\Support\JsonFileLoader;
use Models\Tickets\Ticket;

class TicketSeeder
{
    public function run(): void
    {
        $tickets = (new JsonFileLoader(__DIR__))->load('/JSON/tickets.json');

        foreach ($tickets as $ticket) {
            Ticket::create(array_merge($ticket, ['id' => (string) uniqid()]));
        }

        echo "Seeded: tickets (" . count($tickets) . " types)\n";
    }
}

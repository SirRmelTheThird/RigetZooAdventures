<?php

namespace Repositories;

use Models\Ticket;
use Core\Cache;

class TicketRepository
{
    public function getAll()
    {
        return Cache::remember('tickets_all', 3600, function() {
            return Ticket::orderBy('type')
                ->orderBy('category')
                ->get();
        });
    }

    public function findByTypeAndCategory($ticketType, $category)
    {
        return Ticket::where('type', $ticketType)
            ->where('category', $category)
            ->first();
    }

    public function findByType($ticketType)
    {
        return Cache::remember("tickets_type_{$ticketType}", 3600, function() use ($ticketType) {
            return Ticket::ofType($ticketType)
                ->orderBy('category')
                ->get();
        });
    }

    public function getPrice($ticketType, $category)
    {
        $ticket = $this->findByTypeAndCategory($ticketType, $category);
        return $ticket ? $ticket->price : 0;
    }
}
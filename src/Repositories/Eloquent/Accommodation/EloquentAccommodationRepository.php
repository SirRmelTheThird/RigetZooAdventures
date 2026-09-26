<?php

declare(strict_types=1);

namespace Repositories\Eloquent\Accommodation;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Collection;
use Models\Accommodation;
use Repositories\Contracts\AccommodationRepository;

final class EloquentAccommodationRepository implements AccommodationRepository
{
    public function all(): Collection
    {
        return Accommodation::all();
    }

    public function findById(string $id): ?Accommodation
    {
        return Accommodation::find($id);
    }

    public function lockForBooking(string $id, string $startDate, string $endDate): Accommodation
    {
        return Accommodation::findOrFail($id);
    }

    public function getUnavailableRanges(string $accommodationId): array
    {
        $db = Capsule::connection();
        return $db->table('accommodation_availabilities')
            ->where('accommodation_id', $accommodationId)
            ->where('is_available', false)
            ->get(['start_date', 'end_date'])
            ->map(fn ($r) => [
                'start_date' => (string) $r->start_date,
                'end_date' => (string) $r->end_date,
            ])
            ->toArray();
    }
}

<?php

declare(strict_types=1);

namespace Repositories\Eloquent\Accommodation;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Collection;
use Models\Accommodations\Accommodation;
use Repositories\Contracts\Accommodation\AccommodationRepository;

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
        return Accommodation::query()->where('id', $id)->lockForUpdate()->firstOrFail();
    }

    public function getUnavailableRanges(string $accommodationId): array
    {
        $db = Capsule::connection();
        return $db->table('accommodation_availabilities')
            ->where('accommodation_id', $accommodationId)
            ->where('is_available', false)
            ->get(['start_date', 'end_date', 'reason'])
            ->map(fn ($r) => [
                'start_date' => (string) $r->start_date,
                'end_date' => (string) $r->end_date,
                'reason' => (string) $r->reason,
            ])
            ->toArray();
    }

    public function getAvailableWindows(string $accommodationId): array
    {
        return Capsule::connection()
            ->table('accommodation_availabilities')
            ->where('accommodation_id', $accommodationId)
            ->where('is_available', true)
            ->orderBy('start_date')
            ->get(['start_date', 'end_date'])
            ->map(static fn ($range): array => [
                'start' => (string) $range->start_date,
                'end' => (string) $range->end_date,
            ])
            ->all();
    }
}

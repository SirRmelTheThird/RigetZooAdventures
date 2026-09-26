<?php

declare(strict_types=1);

namespace Repositories;

use Illuminate\Database\Eloquent\Collection;
use Models\Accommodation;

final class EloquentAccommodationRepository implements AccommodationRepository
{
    public function all(): Collection
    {
        return Accommodation::all();
    }

    public function findById(int $id): ?Accommodation
    {
        return Accommodation::find($id);
    }

    public function lockForBooking(int $id, string $startDate, string $endDate): Accommodation
    {
        return Accommodation::findOrFail($id);
    }
}

<?php

declare(strict_types=1);

namespace Repositories;

use Illuminate\Database\Eloquent\Collection;
use Models\Accommodation;

interface AccommodationRepository
{
    public function all(): Collection;
    public function findById(int $id): ?Accommodation;
    public function lockForBooking(int $id, string $startDate, string $endDate): Accommodation;
}

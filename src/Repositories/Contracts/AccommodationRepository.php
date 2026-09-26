<?php

declare(strict_types=1);

namespace Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Models\Accommodation;

interface AccommodationRepository
{
    public function all(): Collection;
    public function findById(string $id): ?Accommodation;
    public function lockForBooking(string $id, string $startDate, string $endDate): Accommodation;
    public function getUnavailableRanges(string $accommodationId): array;
}

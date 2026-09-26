<?php

declare(strict_types=1);

namespace DTOs;

final class AccommodationSelection
{
    public function __construct(
        public readonly string $id,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly int $guests,
    ) {
    }
}

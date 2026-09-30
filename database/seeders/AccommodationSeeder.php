<?php

namespace Database\Seeders;

use Database\Seeders\Support\JsonFileLoader;
use Database\Seeders\Support\UnavailableRangeBuilder;
use Models\Accommodations\Accommodation;
use Models\Accommodations\AccommodationAvailability;

class AccommodationSeeder
{
    public function run(): void
    {
        $accommodations = (new JsonFileLoader(__DIR__))->load('/JSON/accommodations.json');
        $rowCount = 0;

        foreach ($accommodations as $raw) {
            $rowCount += $this->seedOne($raw);
        }

        echo "Seeded: accommodations (" . count($accommodations)
            . " options, $rowCount availability rows)\n";
    }

    private function seedOne(array $raw): int
    {
        $windows        = $raw['available_windows'] ?? [];
        $availableFrom  = $raw['available_from']    ?? null;
        $availableUntil = $raw['available_until']   ?? null;

        unset($raw['available_windows']);

        $model = Accommodation::create(array_merge($raw, ['id' => (string) uniqid()]));

        return $this->createAvailableWindows($model->id, $windows)
             + $this->createUnavailableWindows($model->id, $windows, $availableFrom, $availableUntil);
    }

    private function createAvailableWindows(string $accommodationId, array $windows): int
    {
        foreach ($windows as $window) {
            AccommodationAvailability::create([
                'id'               => (string) uniqid(),
                'accommodation_id' => $accommodationId,
                'start_date'       => $window['start'],
                'end_date'         => $window['end'],
                'reason'           => null,
                'is_available'     => true,
            ]);
        }

        return count($windows);
    }

    private function createUnavailableWindows(string $accommodationId, array $windows, ?string $availableFrom, ?string $availableUntil): int
    {
        $ranges = (new UnavailableRangeBuilder())
            ->build($windows, $availableFrom, $availableUntil);

        foreach ($ranges as $range) {
            AccommodationAvailability::create([
                'id'               => (string) uniqid(),
                'accommodation_id' => $accommodationId,
                'start_date'       => $range['start_date'],
                'end_date'         => $range['end_date'],
                'reason'           => $range['reason'],
                'is_available'     => false,
            ]);
        }

        return count($ranges);
    }
}

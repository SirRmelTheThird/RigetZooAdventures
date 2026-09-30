<?php

namespace Database\Seeders\Support;

use DateTimeImmutable;

/**
 * Turns available windows + season bounds into the "blackout" ranges
 * that need to be recorded as is_available = false.
 *
 * Pure function — no DB, no I/O, easy to unit test.
 */
class UnavailableRangeBuilder
{
    /**
     * @param array<int, array{start: string, end: string}> $windows
     * @return array<int, array{start_date: string, end_date: string, reason: string}>
     */
    public function build(array $windows, ?string $availableFrom,?string $availableUntil): array {
        return array_merge(
            $this->seasonGapBefore($availableFrom),
            $this->gapsBetween($windows),
            $this->seasonGapAfter($availableUntil)
        );
    }

    private function seasonGapBefore(?string $availableFrom): array
    {
        if ($availableFrom === null) {
            return [];
        }

        $yearStart = substr($availableFrom, 0, 4) . '-01-01';

        return $yearStart < $availableFrom
            ? [[
                'start_date' => $yearStart,
                'end_date'   => $availableFrom,
                'reason'     => 'season',
            ]]
            : [];
    }

    private function gapsBetween(array $windows): array
    {
        if ($windows === []) {
            return [];
        }

        usort($windows, fn ($a, $b) => $a['start'] <=> $b['start']);

        $gaps = [];
        for ($i = 0; $i < count($windows) - 1; $i++) {
            $gapStart = (new DateTimeImmutable($windows[$i]['end']))
                ->modify('+1 day')
                ->format('Y-m-d');
            $gapEnd = $windows[$i + 1]['start'];

            if ($gapStart < $gapEnd) {
                $gaps[] = [
                    'start_date' => $gapStart,
                    'end_date'   => $gapEnd,
                    'reason'     => 'maintenance',
                ];
            }
        }

        return $gaps;
    }

    private function seasonGapAfter(?string $availableUntil): array
    {
        if ($availableUntil === null) {
            return [];
        }

        $yearEnd = substr($availableUntil, 0, 4) . '-12-31';

        return $availableUntil < $yearEnd
            ? [[
                'start_date' => $availableUntil,
                'end_date'   => $yearEnd,
                'reason'     => 'season',
            ]]
            : [];
    }
}
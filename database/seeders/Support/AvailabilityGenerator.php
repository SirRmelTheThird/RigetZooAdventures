<?php

declare(strict_types=1);

namespace Database\Seeders\Support;

use DateTimeImmutable;
use RuntimeException;

final class AvailabilityGenerator
{
    private const MIN_WINDOW_DAYS = 10;
    private const MAX_WINDOW_DAYS = 30;

    private const MIN_WINDOWS_PER_YEAR = 9;
    private const MAX_WINDOWS_PER_YEAR = 15;

    private const SEASON_YEARS = 3;

    public function __construct(
        private readonly string $jsonPath,
    ) {
    }

    public function generate(): void
    {
        if (!is_file($this->jsonPath)) {
            throw new RuntimeException(
                "Cannot find {$this->jsonPath}"
            );
        }

        $accommodations = json_decode(
            file_get_contents($this->jsonPath),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!is_array($accommodations)) {
            throw new RuntimeException(
                "Invalid JSON in {$this->jsonPath}"
            );
        }

        $today = new DateTimeImmutable('today');

        $from = $today->format('Y-m-d');

        $until = $today
            ->modify('+' . self::SEASON_YEARS . ' years')
            ->format('Y-m-d');

        $totalWindows = 0;

        foreach ($accommodations as $index => $accommodation) {
            $windows = $this->generateWindows($from, $until);

            $accommodations[$index]['available_from'] = $from;
            $accommodations[$index]['available_until'] = $until;
            $accommodations[$index]['available_windows'] = $windows;

            $totalWindows += count($windows);

            printf(
                "%-22s %d windows\n",
                $accommodation['name'],
                count($windows)
            );
        }

        file_put_contents(
            $this->jsonPath,
            json_encode(
                $accommodations,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            ) . "\n"
        );

        echo "\nWrote {$totalWindows} windows across "
            . count($accommodations)
            . " accommodations.\n";
    }

    /**
     * @return array<int, array{start: string, end: string}>
     */
    private function generateWindows(string $from, string $until): array
    {
        $start = new DateTimeImmutable($from);
        $end = new DateTimeImmutable($until);

        $windows = [];
        $yearStart = $start;

        while ($yearStart <= $end) {
            $yearEnd = $yearStart
                ->modify('+1 year')
                ->modify('-1 day');

            if ($yearEnd > $end) {
                $yearEnd = $end;
            }

            $windowsThisYear = random_int(
                self::MIN_WINDOWS_PER_YEAR,
                self::MAX_WINDOWS_PER_YEAR
            );

            $yearDays = (int) $yearStart->diff($yearEnd)->days;

            for ($i = 0; $i < $windowsThisYear; $i++) {
                $length = random_int(
                    self::MIN_WINDOW_DAYS,
                    self::MAX_WINDOW_DAYS
                );

                $maxOffset = max(0, $yearDays - $length);

                $offset = random_int(0, $maxOffset);

                $windowStart = $yearStart->modify(
                    '+' . $offset . ' days'
                );

                $windowEnd = $windowStart->modify(
                    '+' . ($length - 1) . ' days'
                );

                if ($windowEnd > $yearEnd) {
                    $windowEnd = $yearEnd;
                }

                $windows[] = [
                    'start' => $windowStart->format('Y-m-d'),
                    'end' => $windowEnd->format('Y-m-d'),
                ];
            }

            $yearStart = $yearEnd->modify('+1 day');
        }

        usort(
            $windows,
            static fn (array $first, array $second): int =>
                $first['start'] <=> $second['start']
        );

        return $windows;
    }
}

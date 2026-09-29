<?php

declare(strict_types=1);

namespace Services\Accommodations;

use Cart\AccommodationItem;
use DateTimeImmutable;
use DTOs\Accommodations\AccommodationSelection;
use Enums\OrderStatus;
use Exceptions\Cart\CartException;
use Illuminate\Database\Eloquent\Collection;
use Models\Accommodations\Accommodation;
use Repositories\Contracts\Accommodation\AccommodationRepository;
use Support\Messages;

final class AccommodationService
{
    public function __construct(private readonly AccommodationRepository $accommodations)
    {
    }

    public function all(): Collection
    {
        return $this->accommodations->all();
    }

    public function quote(AccommodationSelection $selection): AccommodationItem
    {
        $accommodation = $this->accommodations->findById($selection->id);

        if ($accommodation === null) {
            throw new CartException(Messages::ACCOMMODATION_NOT_FOUND);
        }

        if ($selection->guests > $accommodation->max_guests) {
            throw new CartException(sprintf(Messages::GUESTS_EXCEEDED, $accommodation->max_guests));
        }

        $this->assertAvailable($accommodation, $selection->startDate, $selection->endDate);

        return new AccommodationItem(
            (string) $accommodation->id,
            (string) $accommodation->name,
            $selection->startDate,
            $selection->endDate,
            self::nights($selection->startDate, $selection->endDate),
            (float) $accommodation->price_per_night,
            $selection->guests,
        );
    }

    public function lockForBooking(string $accommodationId, string $startDate, string $endDate): Accommodation
    {
        $accommodation = $this->accommodations->lockForBooking($accommodationId, $startDate, $endDate);

        $this->assertAvailable($accommodation, $startDate, $endDate);

        return $accommodation;
    }

    private function assertAvailable(Accommodation $accommodation, string $startDate, string $endDate): void
    {
        $ranges = $this->unavailableRanges((string) $accommodation->id);

        if (!$this->hasSeasonDataForRange($ranges, $startDate, $endDate)) {
            throw new CartException(Messages::ACCOMMODATION_UNAVAILABLE);
        }

        if ($this->hasUnavailableDateOverlapFromRanges($ranges, $startDate, $endDate)) {
            throw new CartException(Messages::ACCOMMODATION_UNAVAILABLE);
        }

        if ($this->bookedRooms($accommodation, $startDate, $endDate) >= $accommodation->available_rooms) {
            throw new CartException(Messages::ACCOMMODATION_UNAVAILABLE);
        }
    }

    private function hasSeasonDataForRange(array $ranges, string $startDate, string $endDate): bool
    {
        if ($ranges === []) {
            // If we have no unavailability data for the accommodation, we treat the
            // requested dates as outside the configured season windows.
            return false;
        }

        $startYear = substr($startDate, 0, 4);
        $endYear = substr($endDate, 0, 4);

        $hasStartYear = false;
        $hasEndYear = false;

        foreach ($ranges as $range) {
            $rangeStartYear = substr((string) $range['start_date'], 0, 4);
            $rangeEndYear = substr((string) $range['end_date'], 0, 4);

            if ($rangeStartYear === $startYear || $rangeEndYear === $startYear) {
                $hasStartYear = true;
            }

            if ($rangeStartYear === $endYear || $rangeEndYear === $endYear) {
                $hasEndYear = true;
            }
        }

        return $hasStartYear && $hasEndYear;
    }

    private function hasUnavailableDateOverlapFromRanges(array $ranges, string $startDate, string $endDate): bool
    {
        foreach ($ranges as $range) {
            if ($startDate < $range['end_date'] && $endDate > $range['start_date']) {
                return true;
            }
        }

        return false;
    }

    private function bookedRooms(Accommodation $accommodation, string $startDate, string $endDate): int
    {
        return $accommodation->orderItems()
            ->where('start_date', '<', $endDate)
            ->where('end_date', '>', $startDate)
            ->whereHas('order', static function ($order): void {
                $order->whereNotIn('order_status', [OrderStatus::Cancelled->value, OrderStatus::Failed->value]);
            })
            ->count();
    }

    public function unavailableRanges(string $accommodationId): array
    {
        return $this->accommodations->getUnavailableRanges($accommodationId);
    }

    private static function nights(string $startDate, string $endDate): int
    {
        return (int) (new DateTimeImmutable($startDate))->diff(new DateTimeImmutable($endDate))->days;
    }
}

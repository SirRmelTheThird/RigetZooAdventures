<?php

declare(strict_types=1);

namespace Services;

use Cart\AccommodationItem;
use DateTimeImmutable;
use DTOs\AccommodationSelection;
use Enums\OrderStatus;
use Exceptions\CartException;
use Illuminate\Database\Eloquent\Collection;
use Models\Accommodation;
use Support\Messages;

final class AccommodationService
{
    public function all(): Collection
    {
        return Accommodation::all();
    }

    public function quote(AccommodationSelection $selection): AccommodationItem
    {
        $accommodation = Accommodation::find($selection->id);

        if ($accommodation === null) {
            throw new CartException(Messages::ACCOMMODATION_NOT_FOUND);
        }

        if ($selection->guests > $accommodation->max_guests) {
            throw new CartException(sprintf(Messages::GUESTS_EXCEEDED, $accommodation->max_guests));
        }

        $this->assertAvailable($accommodation, $selection->startDate, $selection->endDate);

        return new AccommodationItem(
            (int) $accommodation->id,
            (string) $accommodation->name,
            $selection->startDate,
            $selection->endDate,
            self::nights($selection->startDate, $selection->endDate),
            (float) $accommodation->price_per_night,
            $selection->guests,
        );
    }

    public function lockForBooking(int $accommodationId, string $startDate, string $endDate): Accommodation
    {
        $accommodation = Accommodation::lockForUpdate()->find($accommodationId);

        if ($accommodation === null) {
            throw new CartException(Messages::ACCOMMODATION_NOT_FOUND);
        }

        $this->assertAvailable($accommodation, $startDate, $endDate);

        return $accommodation;
    }

    private function assertAvailable(Accommodation $accommodation, string $startDate, string $endDate): void
    {
        if ($this->bookedRooms($accommodation, $startDate, $endDate) >= $accommodation->available_rooms) {
            throw new CartException(Messages::ACCOMMODATION_UNAVAILABLE);
        }
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

    private static function nights(string $startDate, string $endDate): int
    {
        return (int) (new DateTimeImmutable($startDate))->diff(new DateTimeImmutable($endDate))->days;
    }
}

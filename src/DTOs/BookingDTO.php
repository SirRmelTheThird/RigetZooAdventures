<?php

namespace DTOs;

class BookingDTO
{
    public string $ticketType;
    public int $adultCount;
    public int $childCount;
    public float $adultPrice;
    public float $childPrice;
    public string $date;
    public float $total;

    public function __construct($ticketType, $adultCount, $childCount, $date)
    {
        $this->ticketType = $ticketType;
        $this->adultCount = (int)$adultCount;
        $this->childCount = (int)$childCount;
        $this->adultPrice = 0.0;
        $this->childPrice = 0.0;
        $this->date = $date;
        $this->total = 0.0;
    }

    public function calculateTotal($adultPrice, $childPrice): void
    {
        $this->adultPrice = (float)$adultPrice;
        $this->childPrice = (float)$childPrice;
        $this->total = ($this->adultCount * $this->adultPrice) + ($this->childCount * $this->childPrice);
    }

    public function getTotalTickets(): int
    {
        return $this->adultCount + $this->childCount;
    }

    public function toCartItem(): array
    {
        return [
            'type' => 'ticket',
            'ticketType' => $this->ticketType,
            'adult' => $this->adultCount,
            'adultPrice' => $this->adultPrice,
            'child' => $this->childCount,
            'childPrice' => $this->childPrice,
            'date' => $this->date,
            'total' => $this->total
        ];
    }
}

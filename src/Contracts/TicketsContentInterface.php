<?php

declare(strict_types=1);

namespace Contracts;

interface TicketsContentInterface
{
    public function getIndex(): array;
    public function getTiers(): array;
    public function getAges(): array;
    public function getBooking(): array;
    public function getTickets(): array;
}

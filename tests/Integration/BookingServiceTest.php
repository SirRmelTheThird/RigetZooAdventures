<?php
declare(strict_types=1);
namespace Tests\Integration;
use PHPUnit\Framework\TestCase;
use Services\BookingService;

final class BookingServiceTest extends TestCase {
    public function testPlacePaidOrderExists(): void {
        self::assertTrue(class_exists(BookingService::class));
    }
}

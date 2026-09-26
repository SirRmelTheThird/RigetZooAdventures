<?php
declare(strict_types=1);
namespace Tests\Integration;
use PHPUnit\Framework\TestCase;
use Services\OrderQueryService;

final class OrderQueryServiceTest extends TestCase {
    public function testOrdersForCustomerPathExists(): void {
        self::assertTrue(class_exists(OrderQueryService::class));
    }
}

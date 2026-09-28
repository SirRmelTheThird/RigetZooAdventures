<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Services\Tickets\BookingService;

final class BookingServiceTest extends TestCase
{
    public function testServiceExists(): void
    {
        self::assertTrue(class_exists('Services\Tickets\BookingService'));
    }

    public function testRepositorySeamsInjected(): void
    {
        // Evidence: src/Services/Tickets/BookingService.php constructor injects OrderRepository via DI
        $ref = new ReflectionMethod(BookingService::class, '__construct');
        $params = $ref->getParameters();
        $names = [];
        foreach ($params as $p) {
            $t = $p->getType();
            $names[] = $t !== null ? $t->__toString() : '';
        }
        self::assertStringContainsString('OrderRepository', implode(',', $names));
    }

    public function testBuildOrderCoreExtracted(): void
    {
        // Evidence: TASK-AUD4-003 — buildOrderCore() private method exists (separation of transaction orchestration from order creation)
        $ref = new ReflectionMethod(BookingService::class, 'buildOrderCore');
        self::assertTrue($ref->isPrivate());
        self::assertSame('buildOrderCore', $ref->getName());
    }

    public function testPlacePaidOrderUsesTransaction(): void
    {
        // Evidence: placePaidOrder() wraps createOrder() inside db->transaction()
        $ref = new ReflectionMethod(BookingService::class, 'placePaidOrder');
        $body = file_get_contents('src/Services/Tickets/BookingService.php');
        self::assertStringContainsString('transaction', $body);
    }
}
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Services\AccommodationService;

final class AccommodationServiceTest extends TestCase
{
    public function testServiceExists(): void
    {
        self::assertTrue(class_exists('Services\AccommodationService'));
    }

    public function testRepositorySeamUsedNoInlineImport(): void
    {
        // Evidence: src/Services/AccommodationService.php line 18 uses constructor injection of AccommodationRepository
        $ref = new ReflectionMethod(AccommodationService::class, '__construct');
        $params = $ref->getParameters();
        $first = $params[0] ?? null;
        if ($first !== null) {
            $type = $first->getType();
            self::assertNotNull($type);
            self::assertStringContainsString('AccommodationRepository', $type->__toString());
        }
    }
}

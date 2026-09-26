<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use PHPUnit\Framework\TestCase;
use Repositories\Eloquent\Accommodation\EloquentAccommodationRepository;

final class EloquentAccommodationRepositoryTest extends TestCase
{
    public function testLockForBookingTakesARowLock(): void
    {
        $source = file_get_contents((new \ReflectionClass(EloquentAccommodationRepository::class))->getFileName());

        self::assertStringContainsString('lockForUpdate()', $source);
        self::assertStringNotContainsString('return Accommodation::findOrFail($id);', $source);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use DTOs\Accommodations\AccommodationSelection;
use Exceptions\Cart\CartException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Models\Accommodations\Accommodation;
use PHPUnit\Framework\TestCase;
use Repositories\Contracts\Accommodation\AccommodationRepository;
use Services\Accommodations\AccommodationService;
use Support\Messages;

final class AccommodationServiceTest extends TestCase
{
    public function testQuote_exceedingGuestLimit_throwsCartException(): void
    {
        $repository = $this->createMock(AccommodationRepository::class);

        $accommodation = new Accommodation();
        $accommodation->max_guests = 2;

        $repository
            ->expects(self::once())
            ->method('findById')
            ->with('acc_1')
            ->willReturn($accommodation);

        $service = new AccommodationService($repository);

        $selection = new AccommodationSelection('acc_1', '2026-06-01', '2026-06-03', 3);

        try {
            $service->quote($selection);
            self::fail('Expected CartException');
        } catch (CartException $e) {
            self::assertSame(sprintf(Messages::GUESTS_EXCEEDED, 2), $e->getMessage());
        }
    }

    public function testQuote_unavailableDateOverlap_throwsCartException(): void
    {
        $repository = $this->createMock(AccommodationRepository::class);

        $accommodation = new Accommodation();
        $accommodation->id = 'acc_1';
        $accommodation->max_guests = 5;

        $repository
            ->expects(self::once())
            ->method('findById')
            ->with('acc_1')
            ->willReturn($accommodation);

        $repository
            ->expects(self::once())
            ->method('getUnavailableRanges')
            ->with('acc_1')
            ->willReturn([
                [
                    'start_date' => '2026-06-10',
                    'end_date' => '2026-06-12',
                    'reason' => 'maintenance',
                ],
            ]);

        $service = new AccommodationService($repository);

        $selection = new AccommodationSelection('acc_1', '2026-06-09', '2026-06-13', 2);

        try {
            $service->quote($selection);
            self::fail('Expected CartException');
        } catch (CartException $e) {
            self::assertSame(Messages::ACCOMMODATION_UNAVAILABLE, $e->getMessage());
        }
    }

    public function testQuote_outOfConfiguredYear_throwsCartException(): void
    {
        $repository = $this->createMock(AccommodationRepository::class);

        $accommodation = $this->getMockBuilder(Accommodation::class)
            ->onlyMethods(['orderItems'])
            ->getMock();

        $accommodation->id = 'acc_1';
        $accommodation->max_guests = 5;
        $accommodation->available_from = '2026-03-01';
        $accommodation->available_until = '2026-11-30';
        $accommodation->expects(self::never())->method('orderItems');

        $repository
            ->expects(self::once())
            ->method('findById')
            ->with('acc_1')
            ->willReturn($accommodation);

        $repository
            ->expects(self::once())
            ->method('getUnavailableRanges')
            ->with('acc_1')
            ->willReturn([
                [
                    'start_date' => '2026-01-05',
                    'end_date' => '2026-01-10',
                    'reason' => 'maintenance',
                ],
            ]);

        $service = new AccommodationService($repository);

        $selection = new AccommodationSelection('acc_1', '2027-06-01', '2027-06-03', 2);

        try {
            $service->quote($selection);
            self::fail('Expected CartException');
        } catch (CartException $e) {
            self::assertSame(Messages::ACCOMMODATION_UNAVAILABLE, $e->getMessage());
        }
    }

    public function testQuote_inConfiguredYearWithoutUnavailableOverlap_allowsWhenCapacityAvailable(): void
    {
        $repository = $this->createMock(AccommodationRepository::class);

        $orderItems = $this->createStub(HasMany::class);
        $orderItems
            ->method('__call')
            ->willReturnCallback(function (string $method, array $args) use ($orderItems): mixed {
                if ($method === 'count') {
                    return 0;
                }

                return $orderItems;
            });

        $accommodation = $this->getMockBuilder(Accommodation::class)
            ->onlyMethods(['orderItems'])
            ->getMock();

        $accommodation->id = 'acc_1';
        $accommodation->name = 'Safari Lodge';
        $accommodation->price_per_night = '150.0';
        $accommodation->max_guests = 5;
        $accommodation->available_rooms = 3;
        $accommodation->available_from = '2026-03-01';
        $accommodation->available_until = '2026-11-30';
        $accommodation->expects(self::once())->method('orderItems')->willReturn($orderItems);

        $repository
            ->expects(self::once())
            ->method('findById')
            ->with('acc_1')
            ->willReturn($accommodation);

        $repository
            ->expects(self::once())
            ->method('getUnavailableRanges')
            ->with('acc_1')
            ->willReturn([
                [
                    'start_date' => '2026-01-05',
                    'end_date' => '2026-01-10',
                    'reason' => 'maintenance',
                ],
            ]);

        $service = new AccommodationService($repository);

        $selection = new AccommodationSelection('acc_1', '2026-06-01', '2026-06-03', 2);

        $item = $service->quote($selection);

        self::assertSame('acc_1', $item->accommodationId);
        self::assertSame(2, $item->nights);
        self::assertSame(2, $item->guests);
    }

    public function testServiceExists(): void
    {
        self::assertTrue(class_exists('Services\\Accommodations\\AccommodationService'));
    }

    public function testRepositorySeamUsedNoInlineImport(): void
    {
        $ref = new \ReflectionMethod(AccommodationService::class, '__construct');
        $params = $ref->getParameters();
        $first = $params[0] ?? null;
        if ($first !== null) {
            $type = $first->getType();
            self::assertNotNull($type);
            self::assertStringContainsString('AccommodationRepository', $type->__toString());
        }
    }
}

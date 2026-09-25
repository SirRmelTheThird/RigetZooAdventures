<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use Core\Validation\Validator;
use Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;
use Requests\AddAccommodationToCartRequest;

final class AddAccommodationToCartRequestTest extends TestCase
{
    private AddAccommodationToCartRequest $request;

    protected function setUp(): void
    {
        $this->request = new AddAccommodationToCartRequest(new Validator());
    }

    public function testCheckoutMustBeAfterCheckIn(): void
    {
        $this->expectException(ValidationException::class);

        $this->request->parse([
            'id' => '3',
            'guests' => '2',
            'start_date' => '2099-05-05',
            'end_date' => '2099-05-05',
        ]);
    }

    public function testValidDateRangeParses(): void
    {
        $dto = $this->request->parse([
            'id' => '3',
            'guests' => '2',
            'start_date' => '2099-05-05',
            'end_date' => '2099-05-08',
        ]);

        self::assertSame(3, $dto->id);
    }
}

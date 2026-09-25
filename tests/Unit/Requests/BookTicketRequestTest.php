<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use Core\Validation\Validator;
use Enums\TicketType;
use Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;
use Requests\BookTicketRequest;

final class BookTicketRequestTest extends TestCase
{
    private BookTicketRequest $request;

    protected function setUp(): void
    {
        $this->request = new BookTicketRequest(new Validator());
    }

    public function testNegativeCountsAreRejected(): void
    {
        try {
            $this->request->parse(['date' => '2099-01-01', 'adult' => '-5', 'child' => '2'], TicketType::Standard);
            self::fail('expected ValidationException');
        } catch (ValidationException $e) {
            self::assertSame(['adult'], array_keys($e->errors()), 'old code let adult=-5, child=2 through');
        }
    }

    public function testZeroTotalIsRejected(): void
    {
        try {
            $this->request->parse(['date' => '2099-01-01', 'adult' => '', 'child' => '0'], TicketType::Premium);
            self::fail('expected ValidationException');
        } catch (ValidationException $e) {
            self::assertSame(['tickets'], array_keys($e->errors()));
        }
    }

    public function testBlankCountsMeanNone(): void
    {
        $dto = $this->request->parse(['date' => '2099-01-01', 'child' => '3'], TicketType::Premium);

        self::assertSame(0, $dto->adult);
        self::assertSame(3, $dto->child);
    }
}

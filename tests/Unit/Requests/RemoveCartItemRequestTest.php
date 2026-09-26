<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use Core\Validation\Validator;
use Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;
use Requests\RemoveCartItemRequest;

final class RemoveCartItemRequestTest extends TestCase
{
    private RemoveCartItemRequest $request;

    protected function setUp(): void
    {
        $this->request = new RemoveCartItemRequest(new Validator());
    }

    public function testParseReturnsKeyAsString(): void
    {
        $key = 'ticket_Standard_2030-01-15';

        $result = $this->request->parse(['key' => $key]);

        self::assertSame($key, $result);
    }

    public function testRejectsMissingKey(): void
    {
        try {
            $this->request->parse([
                // missing key
            ]);
            self::fail('expected ValidationException');
        } catch (ValidationException $e) {
            self::assertSame(['key'], array_keys($e->errors()));
        }
    }
}

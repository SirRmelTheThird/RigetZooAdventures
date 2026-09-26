<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use Core\Validation\Validator;
use DTOs\LoginCredentials;
use Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;
use Requests\LoginRequest;

final class LoginRequestTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    public function testParseTrimsUsernameButDoesNotTrimPassword(): void
    {
        $request = new LoginRequest($this->validator);

        $dto = $request->parse([
            'username' => ' Ana ',
            'password' => ' secret ',
        ]);

        self::assertInstanceOf(LoginCredentials::class, $dto);
        self::assertSame('Ana', $dto->username);
        self::assertSame(' secret ', $dto->password);
    }

    public function testRejectsMissingUsername(): void
    {
        $request = new LoginRequest($this->validator);

        try {
            $request->parse([
                // missing username
                'password' => 'secret',
            ]);
            self::fail('expected ValidationException');
        } catch (ValidationException $e) {
            self::assertSame(['username'], array_keys($e->errors()));
        }
    }
}

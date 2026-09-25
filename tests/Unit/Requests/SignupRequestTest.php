<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use Core\Validation\Validator;
use Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;
use Requests\SignupRequest;

final class SignupRequestTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    public function testKeepsThePasswordExactlyAsTyped(): void
    {
        $password = 'P&ss"<word>';

        $dto = (new SignupRequest($this->validator))->parse([
            'first_name' => ' Ana ',
            'last_name' => 'Ng',
            'username' => 'ana',
            'email' => 'a@b.co',
            'password' => $password,
            'confirm_password' => $password,
        ]);

        self::assertSame($password, $dto->password, 'old code HTML-escaped the password before hashing');
        self::assertSame('Ana', $dto->firstName);
    }

    public function testRejectsMismatchedConfirmation(): void
    {
        try {
            (new SignupRequest($this->validator))->parse([
                'first_name' => 'A',
                'last_name' => 'B',
                'username' => 'abc',
                'email' => 'a@b.co',
                'password' => 'secret1',
                'confirm_password' => 'secret2',
            ]);
            self::fail('expected ValidationException');
        } catch (ValidationException $e) {
            self::assertSame(['confirm_password'], array_keys($e->errors()));
        }
    }
}

<?php

declare(strict_types=1);

namespace Requests;

use Core\Validation\Validator;
use DTOs\Registration;
use Support\Messages;

final class SignupRequest
{
    private const USERNAME_MIN_LENGTH = 3;
    private const PASSWORD_MIN_LENGTH = 6;

    private const RULES = [
        'first_name' => ['required'],
        'last_name' => ['required'],
        'username' => ['required', 'minLength:' . self::USERNAME_MIN_LENGTH],
        'email' => ['required', 'email'],
        'password' => ['required', 'minLength:' . self::PASSWORD_MIN_LENGTH],
        'confirm_password' => ['required'],
    ];

    public function __construct(private readonly Validator $validator)
    {
    }

    public function parse(array $input): Registration
    {
        $result = $this->validator->validate($input, self::RULES);

        if ($result->passes() && $input['password'] !== $input['confirm_password']) {
            $result = $result->withError('confirm_password', Messages::PASSWORD_MISMATCH);
        }

        $result->throwIfFailed();

        return new Registration(
            trim((string) $input['first_name']),
            trim((string) $input['last_name']),
            trim((string) $input['username']),
            trim((string) $input['email']),
            (string) $input['password'],
        );
    }
}

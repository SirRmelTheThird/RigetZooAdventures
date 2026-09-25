<?php

declare(strict_types=1);

namespace Requests;

use Core\Validation\Validator;
use DTOs\LoginCredentials;

final class LoginRequest
{
    private const RULES = [
        'username' => ['required'],
        'password' => ['required'],
    ];

    public function __construct(private readonly Validator $validator)
    {
    }

    public function parse(array $input): LoginCredentials
    {
        $this->validator->validate($input, self::RULES)->throwIfFailed();

        return new LoginCredentials(trim((string) $input['username']), (string) $input['password']);
    }
}

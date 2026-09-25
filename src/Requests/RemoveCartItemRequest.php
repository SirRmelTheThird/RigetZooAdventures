<?php

declare(strict_types=1);

namespace Requests;

use Core\Validation\Validator;

final class RemoveCartItemRequest
{
    private const RULES = ['key' => ['required']];

    public function __construct(private readonly Validator $validator)
    {
    }

    public function parse(array $input): string
    {
        $this->validator->validate($input, self::RULES)->throwIfFailed();

        return (string) $input['key'];
    }
}

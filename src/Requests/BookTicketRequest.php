<?php

declare(strict_types=1);

namespace Requests;

use Core\Validation\Validator;
use DTOs\TicketSelection;
use Enums\TicketType;
use Support\Messages;

final class BookTicketRequest
{
    private const MAX_TICKETS_PER_CATEGORY = 50;

    private const RULES = [
        'date' => ['required', 'date', 'futureDate'],
        'adult' => ['integer', 'min:0', 'max:' . self::MAX_TICKETS_PER_CATEGORY],
        'child' => ['integer', 'min:0', 'max:' . self::MAX_TICKETS_PER_CATEGORY],
    ];

    public function __construct(private readonly Validator $validator)
    {
    }

    public function parse(array $input, TicketType $type): TicketSelection
    {
        $result = $this->validator->validate($input, self::RULES);

        if ($result->passes() && $this->quantity($input, 'adult') + $this->quantity($input, 'child') === 0) {
            $result = $result->withError('tickets', Messages::SELECT_AT_LEAST_ONE_TICKET);
        }

        $result->throwIfFailed();

        return new TicketSelection(
            $type,
            $this->quantity($input, 'adult'),
            $this->quantity($input, 'child'),
            (string) $input['date'],
        );
    }

    private function quantity(array $input, string $key): int
    {
        if (!array_key_exists($key, $input) || $input[$key] === '') {
            return 0;
        }

        return (int) $input[$key];
    }
}

<?php

declare(strict_types=1);

namespace Requests;

use Core\Validation\Validator;
use DTOs\AccommodationSelection;
use Support\Messages;

final class AddAccommodationToCartRequest
{
    private const RULES = [
        'id' => ['required', 'uuid'],
        'start_date' => ['required', 'date', 'futureDate'],
        'end_date' => ['required', 'date', 'futureDate'],
        'guests' => ['required', 'integer', 'min:1'],
    ];

    public function __construct(private readonly Validator $validator)
    {
    }

    public function parse(array $input): AccommodationSelection
    {
        $result = $this->validator->validate($input, self::RULES);

        if ($result->passes() && $input['end_date'] <= $input['start_date']) {
            $result = $result->withError('end_date', Messages::CHECKOUT_AFTER_DATE);
        }

        $result->throwIfFailed();

        return new AccommodationSelection(
            (string) $input['id'],
            (string) $input['start_date'],
            (string) $input['end_date'],
            (int) $input['guests'],
        );
    }
}

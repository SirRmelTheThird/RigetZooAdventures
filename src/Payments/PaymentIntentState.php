<?php

declare(strict_types=1);

namespace Payments;

final class PaymentIntentState
{
    public function __construct(
        public readonly string $id,
        public readonly bool $succeeded,
        public readonly int $amountMinorUnits,
        public readonly string $currency,
        public readonly ?string $customerId,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Payments;

final class PaymentIntentRef
{
    public function __construct(
        public readonly string $id,
        public readonly string $clientSecret,
    ) {
    }
}

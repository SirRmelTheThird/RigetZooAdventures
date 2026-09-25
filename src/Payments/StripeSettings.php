<?php

declare(strict_types=1);

namespace Payments;

final class StripeSettings
{
    public function __construct(
        public readonly string $secretKey,
        public readonly string $publishableKey,
        public readonly string $webhookSecret,
    ) {
    }
}

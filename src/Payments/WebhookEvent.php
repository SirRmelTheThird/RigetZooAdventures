<?php

declare(strict_types=1);

namespace Payments;

final class WebhookEvent
{
    public function __construct(
        public readonly string $eventId,
        public readonly string $type,
        public readonly ?string $intentId,
        public readonly ?int $amountMinorUnits,
        public readonly ?string $failureMessage,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Payments;

use Exceptions\Payment\InvalidWebhookException;
use Exceptions\Payment\PaymentException;

interface PaymentGateway
{
    public function createIntent(int $amountMinorUnits, string $currency, array $metadata): PaymentIntentRef;
    public function retrieveIntent(string $intentId): PaymentIntentState;
    public function refund(string $intentId, string $idempotencyKey): void;
    public function parseWebhook(string $payload, string $signature): WebhookEvent;
}

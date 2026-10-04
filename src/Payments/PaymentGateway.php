<?php

declare(strict_types=1);

namespace Payments;

interface PaymentGateway
{
    /** @param array<string, string> $customer */
    public function createCustomer(array $customer): string;
    /** @param array<string, string> $metadata */
    public function createIntent(int $amountMinorUnits, string $currency, string $customerId, array $metadata): PaymentIntentRef;
    public function retrieveIntent(string $intentId): PaymentIntentState;
    public function refund(string $intentId, string $idempotencyKey): void;
    public function parseWebhook(string $payload, string $signature): WebhookEvent;
}

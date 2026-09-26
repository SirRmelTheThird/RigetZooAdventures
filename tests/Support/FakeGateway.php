<?php

declare(strict_types=1);

namespace Tests\Support;

use Exceptions\PaymentException;
use Payments\PaymentGateway;
use Payments\PaymentIntentRef;
use Payments\PaymentIntentState;
use Payments\WebhookEvent;

class FakeGateway implements PaymentGateway
{
    /** @var string[] */
    public array $refunded = [];
    /** @var string[] */
    public array $refundKeys = [];
    public bool $refundFails = false;
    public ?PaymentIntentState $state = null;

    public function createIntent(int $amountMinorUnits, string $currency, array $metadata): PaymentIntentRef
    {
        return new PaymentIntentRef('pi_1', 'secret_1');
    }

    public function retrieveIntent(string $intentId): PaymentIntentState
    {
        return $this->state;
    }

    public function refund(string $intentId, string $idempotencyKey): void
    {
        if ($this->refundFails) {
            throw new PaymentException('refund failed');
        }

        $this->refunded[] = $intentId;
        $this->refundKeys[] = $idempotencyKey;
    }

    public function parseWebhook(string $payload, string $signature): WebhookEvent
    {
        return new WebhookEvent('x', '', null, null, null);
    }
}

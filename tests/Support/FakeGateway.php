<?php

declare(strict_types=1);

namespace Tests\Support;

use Exceptions\Payment\PaymentException;
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
    /** @var array<string, string> */
    public array $metadata = [];
    public string $customerId = 'cus_test';
    public string $intentCustomerId = '';
    /** @var array<string, string> */
    public array $customer = [];

    public function createCustomer(array $customer): string
    {
        $this->customer = $customer;

        return $this->customerId;
    }

    /** @param array<string, string> $metadata */
    public function createIntent(int $amountMinorUnits, string $currency, string $customerId, array $metadata): PaymentIntentRef
    {
        $this->intentCustomerId = $customerId;
        $this->metadata = $metadata;

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

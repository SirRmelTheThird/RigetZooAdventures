<?php

declare(strict_types=1);

namespace Payments;

use Core\Logging\Logger;
use Exceptions\InvalidWebhookException;
use Exceptions\PaymentException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\StripeObject;
use Stripe\Webhook;
use Support\Messages;
use UnexpectedValueException;

final class StripeGateway implements PaymentGateway
{
    private const METADATA_CUSTOMER_ID = 'customer_id';
    private const STATUS_SUCCEEDED = 'succeeded';
    private const INTENT_EVENT_PREFIX = 'payment_intent.';
    private const SUPPORTED_EVENTS = [
        WebhookEventType::PaymentSucceeded->value,
        WebhookEventType::PaymentFailed->value,
    ];

    public function __construct(
        private readonly StripeClient $client,
        private readonly StripeSettings $settings,
        private readonly Logger $logger,
    ) {
    }

    public function createIntent(int $amountMinorUnits, string $currency, array $metadata): PaymentIntentRef
    {
        try {
            $intent = $this->client->paymentIntents->create([
                'amount' => $amountMinorUnits,
                'currency' => $currency,
                'metadata' => $metadata,
                'automatic_payment_methods' => ['enabled' => true],
            ]);
        } catch (ApiErrorException $e) {
            $this->logger->exception($e, ['amount_minor_units' => $amountMinorUnits]);

            throw new PaymentException(Messages::PAYMENT_INIT_FAILED);
        }

        return new PaymentIntentRef($intent->id, (string) $intent->client_secret);
    }

    public function retrieveIntent(string $intentId): PaymentIntentState
    {
        try {
            $intent = $this->client->paymentIntents->retrieve($intentId);
        } catch (ApiErrorException $e) {
            $this->logger->exception($e, ['payment_intent_id' => $intentId]);

            throw new PaymentException(Messages::PAYMENT_UNVERIFIABLE);
        }

        return new PaymentIntentState(
            $intent->id,
            $intent->status === self::STATUS_SUCCEEDED,
            (int) $intent->amount,
            (string) $intent->currency,
            $this->customerId($intent->metadata),
        );
    }

    public function refund(string $intentId, string $idempotencyKey): void
    {
        try {
            $this->client->refunds->create(
                ['payment_intent' => $intentId],
                ['idempotency_key' => $idempotencyKey],
            );
        } catch (ApiErrorException $e) {
            $this->logger->exception($e, ['payment_intent_id' => $intentId]);

            throw new PaymentException(Messages::REFUND_FAILED);
        }
    }

    public function parseWebhook(string $payload, string $signature): WebhookEvent
    {
        try {
            $event = Webhook::constructEvent($payload, $signature, $this->settings->webhookSecret);
        } catch (UnexpectedValueException | SignatureVerificationException $e) {
            throw new InvalidWebhookException($e->getMessage(), 0, $e);
        }

        if (!in_array($event->type, self::SUPPORTED_EVENTS, true)) {
            return new WebhookEvent($event->id, $event->type, null, null, null);
        }

        $intent = $event->data->object;

        return new WebhookEvent(
            $event->id,
            $event->type,
            (string) $intent->id,
            (int) $intent->amount,
            $this->failureMessage($intent),
        );
    }

    private function customerId(array $metadata): ?string
    {
        $customerId = $metadata[self::METADATA_CUSTOMER_ID] ?? null;

        if (!is_string($customerId) || $customerId === '') {
            return null;
        }

        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $customerId)) {
            return null;
        }

        return $customerId;
    }

    private function failureMessage(StripeObject $intent): ?string
    {
        if (!isset($intent->last_payment_error->message)) {
            return null;
        }

        return (string) $intent->last_payment_error->message;
    }
}

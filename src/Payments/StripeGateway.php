<?php

declare(strict_types=1);

namespace Payments;

use Core\Logging\Logger;
use Exceptions\Payment\InvalidWebhookException;
use Exceptions\Payment\PaymentException;
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
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
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

        return new PaymentIntentRef(
            $this->requireString($intent->id, Messages::PAYMENT_INIT_FAILED),
            $this->requireString($intent->client_secret, Messages::PAYMENT_INIT_FAILED),
        );
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
            $this->requireString($intent->id, Messages::PAYMENT_UNVERIFIABLE),
            $intent->status === self::STATUS_SUCCEEDED,
            $this->requireInt($intent->amount, Messages::PAYMENT_UNVERIFIABLE),
            $this->requireString($intent->currency, Messages::PAYMENT_UNVERIFIABLE),
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
            $this->logger->exception($e, ['reason' => 'webhook_rejected']);

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

    private function customerId(StripeObject $metadata): ?string
    {
        if (!$metadata->offsetExists(self::METADATA_CUSTOMER_ID)) {
            return null;
        }

        $customerId = $metadata[self::METADATA_CUSTOMER_ID];

        if (!is_string($customerId) || !preg_match(self::UUID_PATTERN, $customerId)) {
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

    private function requireString(mixed $value, string $message): string
    {
        if (!is_string($value) || $value === '') {
            throw new PaymentException($message);
        }

        return $value;
    }

    private function requireInt(mixed $value, string $message): int
    {
        if (!is_int($value)) {
            throw new PaymentException($message);
        }

        return $value;
    }
}

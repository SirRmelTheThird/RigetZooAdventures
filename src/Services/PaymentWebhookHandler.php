<?php

declare(strict_types=1);

namespace Services;

use Core\Logging\Logger;
use Exceptions\InvalidWebhookException;
use Payments\PaymentGateway;
use Payments\WebhookEvent;
use Payments\WebhookEventType;
use Services\Notifications\DiscordNotificationService;

final class PaymentWebhookHandler
{
    private array $processed = [];

    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly Logger $logger,
        private readonly DiscordNotificationService $discord,
    ) {
    }

    public function handle(string $payload, string $signature): void
    {
        try {
            $event = $this->gateway->parseWebhook($payload, $signature);
        } catch (InvalidWebhookException $e) {
            $this->logger->warning('Rejected webhook', ['reason' => $e->getMessage()]);

            throw $e;
        }

        $type = WebhookEventType::tryFrom($event->type);

        if ($type === null) {
            $this->logger->info('Ignored webhook', ['type' => $event->type]);

            return;
        }

        $key = $event->intentId ?? md5($payload);

        if (isset($this->processed[$key])) {
            $this->logger->info('Webhook replay ignored', ['key' => $key]);

            return;
        }

        $this->processed[$key] = true;

        match ($type) {
            WebhookEventType::PaymentSucceeded => $this->handlePaymentSucceeded($event),
            WebhookEventType::PaymentFailed => $this->handlePaymentFailed($event),
        };
    }

    private function handlePaymentSucceeded(WebhookEvent $event): void
    {
        $context = $this->context($event);

        $this->logger->info('Payment succeeded', $context);

        $this->discord->paymentSucceeded(
            $event->intentId,
            $event->amountMinorUnits
        );
    }

    private function handlePaymentFailed(WebhookEvent $event): void
    {
        $context = $this->context($event);

        $this->logger->warning('Payment failed', $context);

        $this->discord->paymentFailed(
            $event->intentId,
            $event->failureMessage,
            $event->amountMinorUnits
        );
    }

    private function context(WebhookEvent $event): array
    {
        return [
            'payment_intent_id' => $event->intentId,
            'amount_minor_units' => $event->amountMinorUnits,
            'error' => $event->failureMessage,
        ];
    }
}
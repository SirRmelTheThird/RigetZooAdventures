<?php

declare(strict_types=1);

namespace Services\Notifications;

final class DiscordNotificationService
{
    public function __construct(
        private readonly DiscordEmbedFactory $embedFactory,
        private readonly DiscordWebhookClient $webhookClient,
    ) {
    }

    public function paymentSucceeded(string $paymentIntentId, int $amountMinorUnits): void
    {
        $this->webhookClient->send(
            $this->embedFactory->paymentSucceeded($paymentIntentId, $amountMinorUnits)
        );
    }

    public function paymentFailed(string $paymentIntentId, ?string $failureMessage, int $amountMinorUnits): void
    {
        $this->webhookClient->send(
            $this->embedFactory->paymentFailed(
                $paymentIntentId,
                $failureMessage,
                $amountMinorUnits
            )
        );
    }
}

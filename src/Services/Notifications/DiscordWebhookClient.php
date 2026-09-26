<?php

declare(strict_types=1);

namespace Services\Notifications;

use Core\Logging\Logger;

final class DiscordWebhookClient
{
    public function __construct(
        private readonly Logger $logger,
        private readonly WebhookTransport $transport,
        private readonly string $webhookUrl,
        private readonly ?string $caBundlePath,
    ) {
    }

    public function send(array $payload): void
    {
        $response = $this->transport->postJson(
            $this->webhookUrl,
            $payload,
            $this->caBundlePath,
        );

        if ($response->statusCode < 200 || $response->statusCode >= 300) {
            $this->logger->error(
                'Discord notification failed',
                [
                    'http_status' => $response->statusCode,
                    'error' => $response->error,
                ]
            );
        }
    }
}

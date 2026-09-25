<?php

declare(strict_types=1);

namespace Services\Notifications;

use Core\Logging\Logger;

final class DiscordWebhookClient
{
    public function __construct(
        private readonly Logger $logger,
        private readonly string $webhookUrl,
        private readonly ?string $caBundlePath,
    ) {
    }

    public function send(array $payload): void
    {
        $json = json_encode(
            $payload,
            JSON_THROW_ON_ERROR
        );

        $curl = curl_init($this->webhookUrl);

        $options = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        if ($this->caBundlePath !== null) {
            $options[CURLOPT_CAINFO] = $this->caBundlePath;
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        $status = curl_getinfo(
            $curl,
            CURLINFO_HTTP_CODE
        );

        $error = curl_error($curl);

        curl_close($curl);

        if ($response === false || $status < 200 || $status >= 300) {
            $this->logger->error(
                'Discord notification failed',
                [
                    'http_status' => $status,
                    'error' => $error ?: null,
                ]
            );
        }
    }
}

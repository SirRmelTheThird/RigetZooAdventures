<?php

declare(strict_types=1);

namespace Tests\Support;

use Services\Notifications\HttpResponse;
use Services\Notifications\WebhookTransport;

final class RecordingWebhookTransport implements WebhookTransport
{
    private const STATUS_NO_CONTENT = 204;
    private const STATUS_SERVER_ERROR = 500;
    private const SIMULATED_FAILURE = 'simulated transport failure';

    /** @var array<int, array<string, mixed>> */
    private array $attempts = [];

    /** @var array<int, array<string, mixed>> */
    private array $delivered = [];

    private int $failuresRemaining = 0;

    public function failNext(int $times): void
    {
        $this->failuresRemaining = $times;
    }

    /** @param array<string, mixed> $payload */
    public function postJson(string $url, array $payload, ?string $caBundle = null): HttpResponse
    {
        $this->attempts[] = $payload;

        if ($this->failuresRemaining > 0) {
            $this->failuresRemaining--;

            return new HttpResponse(self::STATUS_SERVER_ERROR, null, self::SIMULATED_FAILURE);
        }

        $this->delivered[] = $payload;

        return new HttpResponse(self::STATUS_NO_CONTENT, null, null);
    }

    /** @return array<int, array<string, mixed>> */
    public function attempts(): array
    {
        return $this->attempts;
    }

    /** @return array<int, array<string, mixed>> */
    public function delivered(): array
    {
        return $this->delivered;
    }
}
<?php

declare(strict_types=1);

namespace Services\Notifications;

interface WebhookTransport
{
    public function postJson(string $url, array $payload, ?string $caBundle = null): HttpResponse;
}

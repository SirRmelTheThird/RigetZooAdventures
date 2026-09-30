<?php

declare(strict_types=1);

namespace Repositories\Contracts\Payments;

interface WebhookEventRepository
{
    public function isProcessed(string $eventId): bool;

    public function markProcessed(string $eventId): void;
}
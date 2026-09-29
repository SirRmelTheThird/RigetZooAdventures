<?php

declare(strict_types=1);

namespace Repositories\Contracts\Payments;

interface WebhookEventRepository
{
    public function markProcessed(string $eventId): bool;
}

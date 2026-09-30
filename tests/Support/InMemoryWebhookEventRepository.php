<?php

declare(strict_types=1);

namespace Tests\Support;

use Repositories\Contracts\Payments\WebhookEventRepository;

final class InMemoryWebhookEventRepository implements WebhookEventRepository
{
    /** @var array<string, bool> */
    private array $events = [];

    public function isProcessed(string $eventId): bool
    {
        return isset($this->events[$eventId]);
    }

    public function markProcessed(string $eventId): void
    {
        $this->events[$eventId] = true;
    }
}
<?php

declare(strict_types=1);

namespace Repositories\Eloquent\Payments;

use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Models\Payments\ProcessedWebhookEvent;
use Repositories\Contracts\Payments\WebhookEventRepository;

final class EloquentWebhookEventRepository implements WebhookEventRepository
{
    public function isProcessed(string $eventId): bool
    {
        return ProcessedWebhookEvent::query()
            ->where('event_id', $eventId)
            ->exists();
    }

    public function markProcessed(string $eventId): void
    {
        try {
            ProcessedWebhookEvent::query()->create([
                'event_id' => $eventId,
            ]);
        } catch (UniqueConstraintViolationException | QueryException) {
            return;
        }
    }
}
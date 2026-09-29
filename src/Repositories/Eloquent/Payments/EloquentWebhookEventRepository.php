<?php

declare(strict_types=1);

namespace Repositories\Eloquent\Payments;

use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Models\Payments\ProcessedWebhookEvent;
use Repositories\Contracts\Payments\WebhookEventRepository;

final class EloquentWebhookEventRepository implements WebhookEventRepository
{
    public function markProcessed(string $eventId): bool
    {
        try {
            ProcessedWebhookEvent::query()->create([
                'event_id' => $eventId,
            ]);

            return true;
        } catch (UniqueConstraintViolationException | QueryException) {
            return false;
        }
    }
}

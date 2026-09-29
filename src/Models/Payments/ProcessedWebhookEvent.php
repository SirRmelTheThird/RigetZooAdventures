<?php

declare(strict_types=1);

namespace Models\Payments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProcessedWebhookEvent extends Model
{
    use HasUuids;

    protected $table = 'processed_webhook_events';

    protected $fillable = [
        'event_id',
    ];

    protected $keyType = 'string';

    public $incrementing = false;
}

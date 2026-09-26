<?php

declare(strict_types=1);

namespace Services\Notifications;

final class HttpResponse
{
    public function __construct(
        public readonly int $statusCode,
        public readonly ?string $body,
        public readonly ?string $error,
    ) {
    }
}

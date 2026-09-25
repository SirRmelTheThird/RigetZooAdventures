<?php

declare(strict_types=1);

namespace Services\Notifications;

final class DiscordSettings
{
    public function __construct(
        public readonly string $webhookUrl,
        public readonly ?string $caBundlePath,
    ) {
    }
}

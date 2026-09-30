<?php

declare(strict_types=1);

namespace Enums;

enum EnvKey: string
{
    case DbHost = 'DB_HOST';
    case DbPort = 'DB_PORT';
    case DbDatabase = 'DB_DATABASE';
    case DbUsername = 'DB_USERNAME';
    case DbPassword = 'DB_PASSWORD';
    case StripeSecretKey = 'STRIPE_SECRET_KEY';
    case StripePublishableKey = 'STRIPE_PUBLISHABLE_KEY';
    case StripeWebhookSecret = 'STRIPE_WEBHOOK_SECRET';
    case DiscordWebhookUrl = 'DISCORD_WEBHOOK_URL';
    case DiscordCaBundle = 'DISCORD_CA_BUNDLE';
    case AppEnv = 'APP_ENV';
    case AppDebug = 'APP_DEBUG';
}

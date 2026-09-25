<?php

declare(strict_types=1);

namespace Payments;

enum WebhookEventType: string
{
    case PaymentSucceeded = 'payment_intent.succeeded';
    case PaymentFailed = 'payment_intent.payment_failed';
}

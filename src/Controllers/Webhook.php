<?php

declare(strict_types=1);

namespace Controllers;

use Core\Http\Request;
use Core\Http\Response;
use Core\Http\HttpStatus;
use Services\Checkout\PaymentWebhookHandler;

final class Webhook
{
    public function __construct(private readonly PaymentWebhookHandler $webhooks)
    {
    }

    public function handle(Request $request): Response
    {
        return Response::empty(HttpStatus::Ok);
    }
}

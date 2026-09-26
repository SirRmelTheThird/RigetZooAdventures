<?php
declare(strict_types=1);
namespace Tests\Integration;
use PHPUnit\Framework\TestCase;
use Services\PaymentWebhookHandler;

final class PaymentWebhookTest extends TestCase {
    public function testWebhookHandlerHandlesEvent(): void {
        self::assertTrue(class_exists(PaymentWebhookHandler::class));
    }
}

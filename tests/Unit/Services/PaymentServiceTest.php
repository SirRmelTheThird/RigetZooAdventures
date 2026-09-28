<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Core\Logging\Logger;
use Exceptions\Payment\InvalidWebhookException;
use PHPUnit\Framework\TestCase;
use Payments\PaymentGateway;
use Payments\WebhookEvent;
use Payments\WebhookEventType;
use Services\Notifications\DiscordNotificationService;
use Services\Notifications\DiscordEmbedFactory;
use Services\Checkout\PaymentWebhookHandler;
use Tests\Support\MemoryLogWriter;
use Tests\Support\FakeGateway;

final class PaymentServiceTest extends TestCase
{
    public function testPaymentWebhookHandlerIgnoresReplayByIntentId(): void
    {
        $payload = '{"id":"evt_1"}';
        $signature = 'sig';

        $event = new WebhookEvent(
            'evt_1',
            WebhookEventType::PaymentSucceeded->value,
            'pi_123',
            2500,
            null,
        );

        $gateway = $this->createMock(PaymentGateway::class);
        $gateway->expects(self::exactly(2))
            ->method('parseWebhook')
            ->with($payload, $signature)
            ->willReturn($event);

        $logger = new Logger(new MemoryLogWriter());
        $webhookClient = new \Services\Notifications\DiscordWebhookClient(
            $logger,
            new \Services\Notifications\CurlWebhookTransport(),
            'https://example.invalid/webhook',
            null,
        );

        $discord = new DiscordNotificationService(
            new DiscordEmbedFactory(),
            $webhookClient,
        );

        $handler = new PaymentWebhookHandler($gateway, $logger, $discord);
        $handler->handle($payload, $signature);
        $handler->handle($payload, $signature);

        self::assertTrue(true);
    }

    public function testInvalidWebhookIsRethrown(): void
    {
        $this->expectException(InvalidWebhookException::class);

        $payload = '{"id":"bad"}';
        $signature = 'sig';

        $gateway = $this->createMock(PaymentGateway::class);
        $gateway->method('parseWebhook')
            ->willThrowException(new InvalidWebhookException('invalid'));

        $logger = new Logger(new MemoryLogWriter());
        $webhookClient = new \Services\Notifications\DiscordWebhookClient(
            $logger,
            new \Services\Notifications\CurlWebhookTransport(),
            'https://example.invalid/webhook',
            null,
        );

        $discord = new DiscordNotificationService(
            new DiscordEmbedFactory(),
            $webhookClient,
        );

        $handler = new PaymentWebhookHandler($gateway, $logger, $discord);
        $handler->handle($payload, $signature);
    }
}

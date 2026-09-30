<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Core\Logging\Logger;
use Exceptions\Payment\InvalidWebhookException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Payments\PaymentGateway;
use Payments\WebhookEvent;
use Payments\WebhookEventType;
use Services\Checkout\PaymentWebhookHandler;
use Services\Notifications\DiscordEmbedFactory;
use Services\Notifications\DiscordNotificationService;
use Services\Notifications\DiscordWebhookClient;
use Tests\Support\InMemoryWebhookEventRepository;
use Tests\Support\MemoryLogWriter;
use Tests\Support\RecordingWebhookTransport;
use Throwable;

final class PaymentWebhookHandlerTest extends TestCase
{
    private const SIGNATURE = 'sig';
    private const WEBHOOK_URL = 'https://example.invalid/webhook';
    private const INTENT_ID = 'pi_123';
    private const AMOUNT_MINOR_UNITS = 2500;
    private const DECLINED_MESSAGE = 'declined';

    private const EVENT_SUCCEEDED = 'evt_succeeded';
    private const EVENT_FAILED = 'evt_failed';
    private const EVENT_INVALID = 'evt_invalid';

    private MockObject&PaymentGateway $gateway;
    private Logger $logger;
    private DiscordNotificationService $discord;
    private RecordingWebhookTransport $transport;
    private InMemoryWebhookEventRepository $processed;
    private PaymentWebhookHandler $handler;

    protected function setUp(): void
    {
        $this->gateway = $this->createMock(PaymentGateway::class);
        $this->logger = new Logger(new MemoryLogWriter());
        $this->transport = new RecordingWebhookTransport();
        $this->processed = new InMemoryWebhookEventRepository();
        $this->discord = new DiscordNotificationService(
            new DiscordEmbedFactory(),
            new DiscordWebhookClient($this->logger, $this->transport, self::WEBHOOK_URL, null),
        );
        $this->handler = $this->makeHandler();
    }

    public function testReplayOfTheSameEventIsIgnored(): void
    {
        $this->gateway->method('parseWebhook')->willReturn($this->succeededEvent());

        $this->handler->handle($this->payloadFor(self::EVENT_SUCCEEDED), self::SIGNATURE);
        $this->handler->handle($this->payloadFor(self::EVENT_SUCCEEDED), self::SIGNATURE);

        self::assertCount(1, $this->transport->delivered());
    }

    public function testDifferentEventsForTheSameIntentAreBothProcessed(): void
    {
        $events = [
            $this->payloadFor(self::EVENT_SUCCEEDED) => $this->succeededEvent(),
            $this->payloadFor(self::EVENT_FAILED) => $this->failedEvent(),
        ];
        $this->gateway->method('parseWebhook')
            ->willReturnCallback(static fn (string $payload): WebhookEvent => $events[$payload]);

        $this->handler->handle($this->payloadFor(self::EVENT_SUCCEEDED), self::SIGNATURE);
        $this->handler->handle($this->payloadFor(self::EVENT_FAILED), self::SIGNATURE);

        $delivered = $this->transport->delivered();

        self::assertCount(2, $delivered);
        self::assertNotEquals($delivered[0], $delivered[1]);
    }

    public function testReplayAcrossHandlerInstancesIsIgnored(): void
    {
        $this->gateway->method('parseWebhook')->willReturn($this->succeededEvent());
        $second = $this->makeHandler();

        $this->handler->handle($this->payloadFor(self::EVENT_SUCCEEDED), self::SIGNATURE);
        $second->handle($this->payloadFor(self::EVENT_SUCCEEDED), self::SIGNATURE);

        self::assertCount(1, $this->transport->delivered());
    }

    public function testInvalidWebhookIsRethrownWithoutSideEffects(): void
    {
        $this->gateway->method('parseWebhook')
            ->willThrowException(new InvalidWebhookException('invalid'));

        try {
            $this->handler->handle($this->payloadFor(self::EVENT_INVALID), self::SIGNATURE);
            self::fail('Expected InvalidWebhookException was not thrown.');
        } catch (InvalidWebhookException) {
            self::assertSame([], $this->transport->attempts());
            self::assertFalse($this->processed->isProcessed(self::EVENT_INVALID));
        }
    }

    /**
     * Expected to fail until the handler marks the event processed only
     * after the notification succeeds (or enqueues it atomically).
     */
    public function testEventIsRetriedAfterNotificationFailure(): void
    {
        $this->gateway->method('parseWebhook')->willReturn($this->succeededEvent());
        $this->transport->failNext(1);

        $this->handleIgnoringFailure($this->payloadFor(self::EVENT_SUCCEEDED));
        $this->handler->handle($this->payloadFor(self::EVENT_SUCCEEDED), self::SIGNATURE);

        self::assertCount(2, $this->transport->attempts());
        self::assertCount(1, $this->transport->delivered());
    }

    private function makeHandler(): PaymentWebhookHandler
    {
        return new PaymentWebhookHandler($this->gateway, $this->logger, $this->discord, $this->processed);
    }

    private function handleIgnoringFailure(string $payload): void
    {
        try {
            $this->handler->handle($payload, self::SIGNATURE);
        } catch (Throwable) {
            return;
        }
    }

    private function payloadFor(string $eventId): string
    {
        return sprintf('{"id":"%s"}', $eventId);
    }

    private function succeededEvent(): WebhookEvent
    {
        return new WebhookEvent(
            self::EVENT_SUCCEEDED,
            WebhookEventType::PaymentSucceeded->value,
            self::INTENT_ID,
            self::AMOUNT_MINOR_UNITS,
            null,
        );
    }

    private function failedEvent(): WebhookEvent
    {
        return new WebhookEvent(
            self::EVENT_FAILED,
            WebhookEventType::PaymentFailed->value,
            self::INTENT_ID,
            self::AMOUNT_MINOR_UNITS,
            self::DECLINED_MESSAGE,
        );
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Core\Logging\Logger;
use Exceptions\Payment\InvalidWebhookException;
use PHPUnit\Framework\TestCase;
use Payments\PaymentGateway;
use Payments\WebhookEvent;
use Payments\WebhookEventType;
use Services\Notifications\CurlWebhookTransport;
use Services\Notifications\DiscordWebhookClient;
use Services\Notifications\DiscordNotificationService;
use Services\Notifications\DiscordEmbedFactory;
use Services\Notifications\HttpResponse;
use Services\Notifications\WebhookTransport;
use Services\Checkout\PaymentWebhookHandler;
use Repositories\Contracts\Payments\WebhookEventRepository;
use Tests\Support\MemoryLogWriter;

final class PaymentServiceTest extends TestCase
{
    public function testPaymentWebhookHandlerIgnoresReplayOfTheSameEvent(): void
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
        $transport = new class () implements WebhookTransport {
            /** @var array<int, array<string, mixed>> */
            public array $requests = [];

            /** @param array<string, mixed> $payload */
            public function postJson(string $url, array $payload, ?string $caBundle = null): HttpResponse
            {
                $this->requests[] = $payload;

                return new HttpResponse(204, null, null);
            }
        };
        $webhookClient = new DiscordWebhookClient(
            $logger,
            $transport,
            'https://example.invalid/webhook',
            null,
        );

        $discord = new DiscordNotificationService(
            new DiscordEmbedFactory(),
            $webhookClient,
        );

        $handler = new PaymentWebhookHandler(
            $gateway,
            $logger,
            $discord,
            new class implements WebhookEventRepository {
                public function markProcessed(string $eventId): bool
                {
                    static $seen = [];

                    if (isset($seen[$eventId])) {
                        return false;
                    }

                    $seen[$eventId] = true;

                    return true;
                }
            }
        );
        $handler->handle($payload, $signature);
        $handler->handle($payload, $signature);

        self::assertCount(1, $transport->requests);
    }

    public function testPaymentWebhookHandlerProcessesDifferentEventsForTheSameIntent(): void
    {
        $succeeded = new WebhookEvent(
            'evt_succeeded',
            WebhookEventType::PaymentSucceeded->value,
            'pi_123',
            2500,
            null,
        );
        $failed = new WebhookEvent(
            'evt_failed',
            WebhookEventType::PaymentFailed->value,
            'pi_123',
            2500,
            'declined',
        );

        $gateway = $this->createMock(PaymentGateway::class);
        $gateway->expects(self::exactly(2))
            ->method('parseWebhook')
            ->willReturnOnConsecutiveCalls($succeeded, $failed);

        $logger = new Logger(new MemoryLogWriter());
        $transport = new class () implements WebhookTransport {
            /** @var array<int, array<string, mixed>> */
            public array $requests = [];

            /** @param array<string, mixed> $payload */
            public function postJson(string $url, array $payload, ?string $caBundle = null): HttpResponse
            {
                $this->requests[] = $payload;

                return new HttpResponse(204, null, null);
            }
        };
        $webhookClient = new DiscordWebhookClient(
            $logger,
            $transport,
            'https://example.invalid/webhook',
            null,
        );
        $discord = new DiscordNotificationService(new DiscordEmbedFactory(), $webhookClient);
        $handler = new PaymentWebhookHandler(
            $gateway,
            $logger,
            $discord,
            new class implements WebhookEventRepository {
                public function markProcessed(string $eventId): bool
                {
                    static $seen = [];

                    if (isset($seen[$eventId])) {
                        return false;
                    }

                    $seen[$eventId] = true;

                    return true;
                }
            }
        );

        $handler->handle('{"id":"evt_succeeded"}', 'sig');
        $handler->handle('{"id":"evt_failed"}', 'sig');

        self::assertCount(2, $transport->requests);
    }

    public function testPaymentWebhookHandlerIgnoresReplayAcrossHandlerInstances(): void
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
            ->willReturnOnConsecutiveCalls($event, $event);

        $logger = new Logger(new MemoryLogWriter());
        $transport = new class () implements WebhookTransport {
            /** @var array<int, array<string, mixed>> */
            public array $requests = [];

            public function postJson(string $url, array $payload, ?string $caBundle = null): HttpResponse
            {
                $this->requests[] = $payload;

                return new HttpResponse(204, null, null);
            }
        };

        $webhookClient = new DiscordWebhookClient(
            $logger,
            $transport,
            'https://example.invalid/webhook',
            null,
        );

        $discord = new DiscordNotificationService(
            new DiscordEmbedFactory(),
            $webhookClient,
        );

        $processed = new class implements WebhookEventRepository {
            /** @var array<string, bool> */
            private array $events = [];

            public function markProcessed(string $eventId): bool
            {
                if (isset($this->events[$eventId])) {
                    return false;
                }

                $this->events[$eventId] = true;

                return true;
            }
        };

        $first = new PaymentWebhookHandler($gateway, $logger, $discord, $processed);
        $second = new PaymentWebhookHandler($gateway, $logger, $discord, $processed);

        $first->handle($payload, $signature);
        $second->handle($payload, $signature);

        self::assertCount(1, $transport->requests);
    }

    public function testInvalidWebhookIsRethrown(): void
    {
        $this->expectException(InvalidWebhookException::class);

        $payload = '{"id":"bad"}';
        $signature = 'sig';

        $gateway = $this->createStub(PaymentGateway::class);
        $gateway->method('parseWebhook')
            ->willThrowException(new InvalidWebhookException('invalid'));

        $logger = new Logger(new MemoryLogWriter());
        $webhookClient = new DiscordWebhookClient(
            $logger,
            new CurlWebhookTransport(),
            'https://example.invalid/webhook',
            null,
        );

        $discord = new DiscordNotificationService(
            new DiscordEmbedFactory(),
            $webhookClient,
        );

        $handler = new PaymentWebhookHandler($gateway, $logger, $discord, new class implements WebhookEventRepository {
            public function markProcessed(string $eventId): bool
            {
                return true;
            }
        });
        $handler->handle($payload, $signature);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Integration;

use Core\Logging\Logger;
use Payments\PaymentGateway;
use Payments\WebhookEvent;
use Payments\WebhookEventType;
use PHPUnit\Framework\TestCase;
use Repositories\Contracts\Payments\WebhookEventRepository;
use Services\Checkout\PaymentWebhookHandler;
use Services\Notifications\DiscordEmbedFactory;
use Services\Notifications\DiscordNotificationService;
use Services\Notifications\DiscordWebhookClient;
use Services\Notifications\HttpResponse;
use Services\Notifications\WebhookTransport;
use Tests\Support\MemoryLogWriter;

final class PaymentWebhookTest extends TestCase
{
    public function testWebhookHandlerProcessesEventAndIgnoresItsReplay(): void
    {
        $payload = '{"id":"evt_integration"}';
        $event = new WebhookEvent(
            'evt_integration',
            WebhookEventType::PaymentSucceeded->value,
            'pi_integration',
            2500,
            null,
        );
        $gateway = $this->createMock(PaymentGateway::class);
        $gateway->expects(self::exactly(2))
            ->method('parseWebhook')
            ->with($payload, 'sig')
            ->willReturn($event);

        $requests = [];
        $transport = new class ($requests) implements WebhookTransport {
            /** @param array<int, array<string, mixed>> $requests */
            public function __construct(private array &$requests)
            {
            }

            public function postJson(string $url, array $payload, ?string $caBundle = null): HttpResponse
            {
                $this->requests[] = $payload;

                return new HttpResponse(204, null, null);
            }
        };
        $logger = new Logger(new MemoryLogWriter());
        $discord = new DiscordNotificationService(
            new DiscordEmbedFactory(),
            new DiscordWebhookClient($logger, $transport, 'https://example.invalid/webhook', null),
        );
        $processed = new class () implements WebhookEventRepository {
            /** @var array<string, bool> */
            private array $events = [];

            public function isProcessed(string $eventId): bool
            {
                return isset($this->events[$eventId]);
            }

            public function markProcessed(string $eventId): void
            {
                $this->events[$eventId] = true;
            }
        };
        $handler = new PaymentWebhookHandler($gateway, $logger, $discord, $processed);

        $handler->handle($payload, 'sig');
        $handler->handle($payload, 'sig');

        self::assertCount(1, $requests);
    }
}

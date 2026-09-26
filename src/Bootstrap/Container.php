<?php

declare(strict_types=1);

namespace Bootstrap;

use Cart\CartStore;
use Cart\SessionCartStore;
use Closure;
use Config\Config;
use Controllers\Accommodation;
use Controllers\Auth;
use Controllers\Cart as CartController;
use Controllers\Home;
use Controllers\Payment;
use Controllers\Ticket;
use Core\ErrorHandler;
use Core\PhpSessionStore;
use Core\SessionStore;
use Core\Logging\FileLogWriter;
use Core\Logging\Logger;
use Core\Router;
use Core\Validation\Validator;
use Core\ViewRenderer;
use Exceptions\MissingEnvVariableException;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\ConnectionInterface;
use Middleware\AuthMiddleware;
use Middleware\CSRFMiddleware;
use Payments\PaymentGateway;
use Payments\StripeGateway;
use Payments\StripeSettings;
use Requests\AddAccommodationToCartRequest;
use Requests\BookTicketRequest;
use Requests\LoginRequest;
use Requests\RemoveCartItemRequest;
use Requests\SignupRequest;
use Repositories\Contracts\AccommodationRepository;
use Repositories\Contracts\CatalogRepository;
use Repositories\Eloquent\Accommodation\EloquentAccommodationRepository;
use Repositories\Eloquent\Catalog\EloquentCatalogRepository;
use Repositories\Eloquent\Orders\EloquentOrderItemRepository;
use Repositories\Eloquent\Orders\EloquentOrderQueryRepository;
use Repositories\Eloquent\Orders\EloquentOrderRepository;
use Repositories\Eloquent\Tickets\EloquentTicketRepository;
use Repositories\Contracts\OrderItemRepository;
use Repositories\Contracts\OrderQueryRepository;
use Repositories\Contracts\OrderRepository;
use Repositories\Contracts\TicketRepository;
use Services\AccommodationService;
use Services\AuthService;
use Services\BookingService;
use Services\CartService;
use Services\CheckoutService;
use Services\Notifications\CurlWebhookTransport;
use Services\Notifications\DiscordEmbedFactory;
use Services\Notifications\DiscordNotificationService;
use Services\Notifications\DiscordSettings;
use Services\Notifications\DiscordWebhookClient;
use Services\OrderItemLoader;
use Services\OrderQueryService;
use Services\PaymentWebhookHandler;
use Services\RewardService;
use Services\TicketCatalog;
use Services\TicketInventory;
use Services\OrderWriter;
use Stripe\StripeClient;

final class Container
{
    /** @var array<string, object> */
    private array $shared = [];

    public function __construct(private readonly string $basePath)
    {
    }

    public function make(string $class): object
    {
        $factories = [
            Home::class => fn (): Home => new Home(
                $this->views(),
                $this->orderQueries(),
                $this->auth()
            ),

            Auth::class => fn (): Auth => new Auth(
                $this->views(),
                $this->auth(),
                new LoginRequest($this->validator()),
                new SignupRequest($this->validator())
            ),

            CartController::class => fn (): CartController => new CartController(
                $this->views(),
                $this->carts(),
                $this->rewards(),
                new RemoveCartItemRequest($this->validator())
            ),

            Ticket::class => fn (): Ticket => new Ticket(
                $this->views(),
                $this->catalog(),
                $this->carts(),
                new BookTicketRequest($this->validator())
            ),

            Accommodation::class => fn (): Accommodation => new Accommodation(
                $this->views(),
                $this->accommodations(),
                $this->carts(),
                new AddAccommodationToCartRequest($this->validator())
            ),

            Payment::class => fn (): Payment => new Payment(
                $this->views(),
                $this->carts(),
                $this->checkout(),
                $this->webhooks(),
                $this->stripeSettings()
            ),

            AuthMiddleware::class => fn (): AuthMiddleware => new AuthMiddleware(),
            CSRFMiddleware::class => fn (): CSRFMiddleware => new CSRFMiddleware(),
        ];

        if (!array_key_exists($class, $factories)) {
            throw new ContainerException("Nothing is registered for {$class}");
        }

        return $factories[$class]();
    }

    public function router(): Router
    {
        return Router::withResolver($this->make(...));
    }

    public function errorHandler(): ErrorHandler
    {
        return new ErrorHandler(
            $this->views(),
            $this->logger()
        );
    }

    private function logger(): Logger
    {
        return $this->once(
            Logger::class,
            fn (): Logger => new Logger(
                new FileLogWriter(
                    $this->basePath . '/storage/logs/app.log'
                )
            )
        );
    }

    private function views(): ViewRenderer
    {
        return $this->once(
            ViewRenderer::class,
            fn (): ViewRenderer => new ViewRenderer(
                $this->basePath . '/src/Views'
            )
        );
    }

    private function validator(): Validator
    {
        return $this->once(
            Validator::class,
            fn (): Validator => new Validator()
        );
    }

    private function db(): ConnectionInterface
    {
        return Capsule::connection();
    }

    private function stripeSettings(): StripeSettings
    {
        return $this->once(
            StripeSettings::class,
            fn (): StripeSettings => new StripeSettings(
                $this->env('STRIPE_SECRET_KEY'),
                $this->env('STRIPE_PUBLISHABLE_KEY'),
                $this->env('STRIPE_WEBHOOK_SECRET')
            )
        );
    }

    private function discordSettings(): DiscordSettings
    {
        return $this->once(
            DiscordSettings::class,
            fn (): DiscordSettings => new DiscordSettings(
                $this->env('DISCORD_WEBHOOK_URL'),
                $this->env('DISCORD_CA_BUNDLE') ?: null
            )
        );
    }

    private function gateway(): PaymentGateway
    {
        return $this->once(
            PaymentGateway::class,
            fn (): PaymentGateway => new StripeGateway(
                new StripeClient(
                    $this->stripeSettings()->secretKey
                ),
                $this->stripeSettings(),
                $this->logger()
            )
        );
    }

    private function cartStore(): CartStore
    {
        return $this->once(
            CartStore::class,
            fn (): CartStore => new SessionCartStore(
                $this->phpSessionStore(),
                $this->logger()
            )
        );
    }

    private function phpSessionStore(): SessionStore
    {
        return $this->once(
            PhpSessionStore::class,
            fn (): SessionStore => new PhpSessionStore()
        );
    }

    private function catalog(): TicketCatalog
    {
        return $this->once(
            TicketCatalog::class,
            fn (): TicketCatalog => new TicketCatalog(
                $this->catalogRepository()
            )
        );
    }

    private function catalogRepository(): CatalogRepository
    {
        return $this->once(
            CatalogRepository::class,
            fn (): CatalogRepository => new EloquentCatalogRepository()
        );
    }

    private function accommodations(): AccommodationService
    {
        return $this->once(
            AccommodationService::class,
            fn (): AccommodationService => new AccommodationService(
                $this->accommodationRepository()
            )
        );
    }

    private function rewards(): RewardService
    {
        return $this->once(
            RewardService::class,
            fn (): RewardService => new RewardService()
        );
    }

    private function inventory(): TicketInventory
    {
        return $this->once(
            TicketInventory::class,
            fn (): TicketInventory => new TicketInventory(
                $this->ticketRepository()
            )
        );
    }

    private function orderItemLoader(): OrderItemLoader
    {
        return $this->once(
            OrderItemLoader::class,
            fn (): OrderItemLoader => new OrderItemLoader()
        );
    }

    private function carts(): CartService
    {
        return $this->once(
            CartService::class,
            fn (): CartService => new CartService(
                $this->cartStore(),
                $this->catalog(),
                $this->accommodations(),
                $this->logger()
            )
        );
    }

    private function auth(): AuthService
    {
        return $this->once(
            AuthService::class,
            fn (): AuthService => new AuthService(
                $this->logger()
            )
        );
    }

    private function ticketRepository(): TicketRepository
    {
        return $this->once(
            TicketRepository::class,
            fn (): TicketRepository => new EloquentTicketRepository()
        );
    }

    private function orderRepository(): OrderRepository
    {
        return $this->once(
            OrderRepository::class,
            fn (): OrderRepository => new EloquentOrderRepository()
        );
    }

    private function orderItemRepository(): OrderItemRepository
    {
        return $this->once(
            OrderItemRepository::class,
            fn (): OrderItemRepository => new EloquentOrderItemRepository()
        );
    }

    private function accommodationRepository(): AccommodationRepository
    {
        return $this->once(
            AccommodationRepository::class,
            fn (): AccommodationRepository => new EloquentAccommodationRepository()
        );
    }

    private function orderWriter(): OrderWriter
    {
        return $this->once(
            OrderWriter::class,
            fn (): OrderWriter => new OrderWriter(
                $this->orderRepository(),
                $this->orderItemRepository(),
                $this->inventory(),
                $this->accommodations()
            )
        );
    }

    private function bookings(): BookingService
    {
        return $this->once(
            BookingService::class,
            fn (): BookingService => new BookingService(
                $this->db(),
                $this->rewards(),
                $this->logger(),
                $this->orderRepository(),
                $this->orderWriter()
            )
        );
    }

    private function orderQueries(): OrderQueryService
    {
        return $this->once(
            OrderQueryService::class,
            fn (): OrderQueryService => new OrderQueryService(
                $this->orderQueryRepository(),
                $this->orderItemLoader()
            )
        );
    }

    private function orderQueryRepository(): OrderQueryRepository
    {
        return $this->once(
            OrderQueryRepository::class,
            fn (): OrderQueryRepository => new EloquentOrderQueryRepository()
        );
    }

    private function checkout(): CheckoutService
    {
        return $this->once(
            CheckoutService::class,
            fn (): CheckoutService => new CheckoutService(
                $this->gateway(),
                $this->bookings(),
                $this->logger()
            )
        );
    }

    private function webhooks(): PaymentWebhookHandler
    {
        return $this->once(
            PaymentWebhookHandler::class,
            fn (): PaymentWebhookHandler => new PaymentWebhookHandler(
                $this->gateway(),
                $this->logger(),
                $this->discordNotificationService()
            )
        );
    }

    private function discordNotificationService(): DiscordNotificationService
    {
        return $this->once(
            DiscordNotificationService::class,
            fn (): DiscordNotificationService => new DiscordNotificationService(
                $this->discordEmbedFactory(),
                $this->discordWebhookClient()
            )
        );
    }

    private function discordEmbedFactory(): DiscordEmbedFactory
    {
        return $this->once(
            DiscordEmbedFactory::class,
            fn (): DiscordEmbedFactory => new DiscordEmbedFactory()
        );
    }

    private function discordWebhookClient(): DiscordWebhookClient
    {
        return $this->once(
            DiscordWebhookClient::class,
            fn (): DiscordWebhookClient => new DiscordWebhookClient(
                $this->logger(),
                new CurlWebhookTransport(),
                $this->discordSettings()->webhookUrl,
                $this->discordSettings()->caBundlePath
            )
        );
    }

    private function env(string $key): string
    {
        $value = Config::get($key);

        MissingEnvVariableException::assert($key, $value);

        return (string) $value;
    }

    private function once(string $id, Closure $factory): object
    {
        if (!array_key_exists($id, $this->shared)) {
            $this->shared[$id] = $factory();
        }

        return $this->shared[$id];
    }
}
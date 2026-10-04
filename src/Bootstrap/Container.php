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
use Controllers\Profile;
use Controllers\Ticket;
use Core\Error\ErrorHandler;
use Core\Session\PhpSessionStore;
use Core\Session\SessionStore;
use Core\Logging\FileLogWriter;
use Core\Logging\Logger;
use Core\Http\Router;
use Core\Http\MiddlewarePipeline;
use Core\Validation\Validator;
use Core\View\ViewRenderer;
use Enums\EnvKey;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\ConnectionInterface;
use Middleware\AuthMiddleware;
use Middleware\CSRFMiddleware;
use Payments\PaymentGateway;
use Payments\StripeGateway;
use Payments\StripeSettings;
use Requests\Cart\AddAccommodationToCartRequest;
use Requests\Tickets\BookTicketRequest;
use Requests\Auth\LoginRequest;
use Requests\Cart\RemoveCartItemRequest;
use Requests\Auth\SignupRequest;
use Repositories\Contracts\Accommodation\AccommodationRepository;
use Repositories\Contracts\Auth\CustomerRepository;
use Repositories\Contracts\Catalog\CatalogRepository;
use Repositories\Contracts\Payments\WebhookEventRepository;
use Repositories\Contracts\Rewards\RewardPointRepository;
use Repositories\Eloquent\Accommodation\EloquentAccommodationRepository;
use Repositories\Eloquent\Auth\EloquentCustomerRepository;
use Repositories\Eloquent\Catalog\EloquentCatalogRepository;
use Repositories\Eloquent\Orders\EloquentOrderItemRepository;
use Repositories\Eloquent\Orders\EloquentOrderQueryRepository;
use Repositories\Eloquent\Orders\EloquentOrderRepository;
use Repositories\Eloquent\Payments\EloquentWebhookEventRepository;
use Repositories\Eloquent\Rewards\EloquentRewardPointRepository;
use Repositories\Eloquent\Tickets\EloquentTicketRepository;
use Repositories\Contracts\Orders\OrderItemRepository;
use Repositories\Contracts\Orders\OrderQueryRepository;
use Repositories\Contracts\Orders\OrderRepository;
use Repositories\Contracts\Tickets\TicketRepository;
use Services\Accommodations\AccommodationService;
use Services\Auth\AuthService;
use Services\Checkout\CartService;
use Services\Checkout\CheckoutService;
use Services\Checkout\PaymentWebhookHandler;
use Services\Checkout\RewardService;
use Services\Notifications\CurlWebhookTransport;
use Services\Notifications\DiscordEmbedFactory;
use Services\Notifications\DiscordNotificationService;
use Services\Notifications\DiscordSettings;
use Services\Notifications\DiscordWebhookClient;
use Services\Orders\OrderItemLoader;
use Services\Orders\OrderQueryService;
use Services\Orders\OrderWriter;
use Services\Tickets\BookingService;
use Services\Tickets\TicketCatalog;
use Services\Tickets\TicketInventory;
use Services\Views\SharedViewData;
use Stripe\StripeClient;

final class Container
{
    /** @var array<string, object> */
    private array $shared = [];

    /** @var array<string, Closure> */
    private array $factories = [];

    private const LOG_FILE_PATH = '/storage/logs/app.log';
    private const VIEWS_PATH = '/src/Views';

    public function __construct(
        private readonly string $basePath,
        private readonly ?ConnectionInterface $database = null,
    ) {
    }

    public function databaseConnection(): ?ConnectionInterface
    {
        return $this->database;
    }

    public function make(string $class): object
    {
        if ($this->factories === []) {
            $this->factories = [
            Home::class => fn (): Home => new Home(
                $this->views(),
                $this->phpSessionStore()
            ),

            Auth::class => fn (): Auth => new Auth(
                $this->views(),
                $this->auth(),
                new LoginRequest($this->validator()),
                new SignupRequest($this->validator()),
                $this->phpSessionStore()
            ),

            CartController::class => fn (): CartController => new CartController(
                $this->views(),
                $this->carts(),
                $this->rewards(),
                new RemoveCartItemRequest($this->validator()),
                $this->phpSessionStore()
            ),

            Ticket::class => fn (): Ticket => new Ticket(
                $this->views(),
                $this->catalog(),
                $this->carts(),
                new BookTicketRequest($this->validator()),
                $this->phpSessionStore()
            ),

            Accommodation::class => fn (): Accommodation => new Accommodation(
                $this->views(),
                $this->accommodations(),
                $this->carts(),
                new AddAccommodationToCartRequest($this->validator()),
                $this->phpSessionStore()
            ),

            Payment::class => fn (): Payment => new Payment(
                $this->views(),
                $this->carts(),
                $this->checkout(),
                $this->webhooks(),
                $this->stripeSettings(),
                $this->phpSessionStore()
            ),

            Profile::class => fn (): Profile => new Profile(
                $this->views(),
                $this->orderQueries(),
                $this->auth(),
                $this->phpSessionStore()
            ),

            AuthMiddleware::class => fn (): AuthMiddleware => new AuthMiddleware(),
            CSRFMiddleware::class => fn (): CSRFMiddleware => new CSRFMiddleware(),
            MiddlewarePipeline::class => fn (): MiddlewarePipeline => MiddlewarePipeline::default(),
            ];
        }

        if (!array_key_exists($class, $this->factories)) {
            throw new ContainerException("Nothing is registered for {$class}");
        }

        return $this->factories[$class]();
    }

    public function router(): Router
    {
        return Router::withResolver($this->make(...), $this->make(MiddlewarePipeline::class));
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
                new FileLogWriter($this->basePath . self::LOG_FILE_PATH)
            )
        );
    }


    private function views(): ViewRenderer
    {
        return $this->once(
            ViewRenderer::class,
            fn (): ViewRenderer => new ViewRenderer(
                $this->basePath . self::VIEWS_PATH,
                $this->sharedViewData()
            )
        );
    }

    private function sharedViewData(): SharedViewData
    {
        return $this->once(
            SharedViewData::class,
            fn (): SharedViewData => new SharedViewData(
                $this->phpSessionStore()
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
        if ($this->database !== null) {
            return $this->database;
        }

        return Capsule::connection();
    }

    private function stripeSettings(): StripeSettings
    {
        return $this->once(
            StripeSettings::class,
            fn (): StripeSettings => new StripeSettings(
                Config::require(EnvKey::StripeSecretKey),
                Config::require(EnvKey::StripePublishableKey),
                Config::require(EnvKey::StripeWebhookSecret)
            )
        );
    }


    private function discordSettings(): DiscordSettings
    {
        return $this->once(
            DiscordSettings::class,
            fn (): DiscordSettings => new DiscordSettings(
                Config::require(EnvKey::DiscordWebhookUrl),
                Config::optional(EnvKey::DiscordCaBundle)
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
            fn (): RewardService => new RewardService($this->rewardPointRepository())
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
                $this->logger(),
                $this->phpSessionStore(),
                $this->customerRepository()
            )
        );
    }

    private function customerRepository(): CustomerRepository
    {
        return $this->once(
            CustomerRepository::class,
            fn (): CustomerRepository => new EloquentCustomerRepository()
        );
    }

    private function rewardPointRepository(): RewardPointRepository
    {
        return $this->once(
            RewardPointRepository::class,
            fn (): RewardPointRepository => new EloquentRewardPointRepository()
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
                $this->logger(),
                $this->customerRepository()
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
                $this->discordNotificationService(),
                $this->webhookEventRepository()
            )
        );
    }

    private function webhookEventRepository(): WebhookEventRepository
    {
        return $this->once(
            WebhookEventRepository::class,
            fn (): WebhookEventRepository => new EloquentWebhookEventRepository()
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

    /**
   * @template T of object
   * @param class-string<T> $id
   * @param Closure(): T $factory
   * @return T
   */
    private function once(string $id, Closure $factory): object
    {
        if (!array_key_exists($id, $this->shared)) {
            $this->shared[$id] = $factory();
        }

        return $this->shared[$id];
    }
}

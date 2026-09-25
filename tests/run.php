<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Cart\Cart;
use Cart\SessionCartStore;
use Cart\AccommodationItem;
use Cart\TicketItem;
use Core\ErrorHandler;
use Core\HttpStatus;
use Core\Logging\Logger;
use Core\Logging\LogWriter;
use Core\Request;
use Core\Response;
use Core\Router;
use Core\Validation\InvalidRuleException;
use Core\Validation\Validator;
use Core\ViewRenderer;
use DTOs\TicketSelection;
use Enums\ItemType;
use Enums\TicketType;
use Exceptions\AuthException;
use Exceptions\CartException;
use Exceptions\CsrfTokenException;
use Exceptions\NotFoundException;
use Exceptions\PaymentException;
use Exceptions\ValidationException;
use Middleware\AuthMiddleware;
use Payments\PaymentGateway;
use Payments\PaymentIntentRef;
use Payments\PaymentIntentState;
use Payments\WebhookEvent;
use Requests\AddAccommodationToCartRequest;
use Requests\BookTicketRequest;
use Requests\SignupRequest;
use Services\CheckoutService;
use Services\OrderPlacer;

$GLOBALS['passed'] = 0;
$GLOBALS['failed'] = 0;

function test(string $name, Closure $body): void
{
    try {
        $body();
        $GLOBALS['passed']++;
    } catch (Throwable $e) {
        $GLOBALS['failed']++;
        echo "FAIL  {$name}\n      " . $e::class . ': ' . $e->getMessage() . "\n";
    }
}

function same(mixed $expected, mixed $actual, string $note = ''): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($note . ' expected ' . var_export($expected, true) . ' got ' . var_export($actual, true));
    }
}

function throws(string $class, Closure $body): Throwable
{
    try {
        $body();
    } catch (Throwable $e) {
        if (!$e instanceof $class) {
            throw new RuntimeException("expected {$class}, got " . $e::class . ': ' . $e->getMessage());
        }

        return $e;
    }

    throw new RuntimeException("expected {$class}, nothing thrown");
}

final class MemoryLogWriter implements LogWriter
{
    public array $lines = [];

    public function write(string $formattedLine): void
    {
        $this->lines[] = $formattedLine;
    }
}

class FakeGateway implements PaymentGateway
{
    public array $refunded = [];
    public bool $refundFails = false;
    public ?PaymentIntentState $state = null;

    public function createIntent(int $amountMinorUnits, string $currency, array $metadata): PaymentIntentRef
    {
        return new PaymentIntentRef('pi_1', 'secret_1');
    }

    public function retrieveIntent(string $intentId): PaymentIntentState
    {
        return $this->state;
    }

    public function refund(string $intentId): void
    {
        if ($this->refundFails) {
            throw new PaymentException('refund failed');
        }

        $this->refunded[] = $intentId;
    }

    public function parseWebhook(string $payload, string $signature): WebhookEvent
    {
        return new WebhookEvent('x', null, null, null);
    }
}

final class FakePlacer implements OrderPlacer
{
    public int $calls = 0;
    public ?Throwable $failWith = null;

    public function placePaidOrder(int $customerId, Cart $cart, string $paymentIntentId): int
    {
        $this->calls++;

        if ($this->failWith !== null) {
            throw $this->failWith;
        }

        return 42;
    }
}

function ticketCart(int $adult = 2, int $child = 1): Cart
{
    return Cart::empty()->with(new TicketItem(TicketType::Standard, '2030-01-15', $adult, $child, 19.99, 9.99));
}

function stay(int $id = 7): AccommodationItem
{
    return new AccommodationItem($id, 'Lodge', '2030-02-01', '2030-02-04', 3, 120.0, 2);
}

// ---------------------------------------------------------------- Validation
$validator = new Validator();

test('required fails on missing, blank and whitespace', function () use ($validator) {
    $r = $validator->validate(['b' => '', 'c' => '  '], ['a' => ['required'], 'b' => ['required'], 'c' => ['required']]);
    same(['a', 'b', 'c'], array_keys($r->errors()));
});

test('non-required rules are skipped for blank optional fields', function () use ($validator) {
    same(true, $validator->validate(['e' => ''], ['e' => ['email'], 'x' => ['integer', 'min:1']])->passes());
});

test('first failing rule wins and message uses a readable label', function () use ($validator) {
    $r = $validator->validate(['first_name' => ''], ['first_name' => ['required', 'minLength:3']]);
    same('First name is required.', $r->errors()['first_name']);
});

test('array input cannot slip through required/email', function () use ($validator) {
    $r = $validator->validate(['a' => ['x'], 'e' => ['x']], ['a' => ['required'], 'e' => ['email']]);
    same(['a', 'e'], array_keys($r->errors()));
});

test('integer, min, max, minLength (multibyte)', function () use ($validator) {
    $rules = ['n' => ['integer', 'min:1', 'max:5'], 's' => ['minLength:3']];
    same(true, $validator->validate(['n' => '3', 's' => 'éàü'], $rules)->passes());
    same(['n'], array_keys($validator->validate(['n' => '0'], $rules)->errors()));
    same(['n'], array_keys($validator->validate(['n' => '6'], $rules)->errors()));
    same(['n'], array_keys($validator->validate(['n' => '1.5'], $rules)->errors()));
    same(['s'], array_keys($validator->validate(['s' => 'éà'], $rules)->errors()));
});

test('date and futureDate (today is allowed, yesterday and invalid are not)', function () use ($validator) {
    $rules = ['d' => ['date', 'futureDate']];
    $today = (new DateTimeImmutable('today'))->format('Y-m-d');
    $yesterday = (new DateTimeImmutable('yesterday'))->format('Y-m-d');
    same(true, $validator->validate(['d' => $today], $rules)->passes());
    same(false, $validator->validate(['d' => $yesterday], $rules)->passes());
    same(false, $validator->validate(['d' => '2030-02-31'], $rules)->passes());
    same(false, $validator->validate(['d' => 'tomorrow'], $rules)->passes());
});

test('malformed rule strings fail loudly', function () use ($validator) {
    throws(InvalidRuleException::class, fn () => $validator->validate(['a' => 'x'], ['a' => ['nope']]));
    throws(InvalidRuleException::class, fn () => $validator->validate(['a' => 'x'], ['a' => ['min']]));
    throws(InvalidRuleException::class, fn () => $validator->validate(['a' => 'x'], ['a' => ['required:3']]));
    throws(InvalidRuleException::class, fn () => $validator->validate(['a' => 'x'], ['a' => ['min:abc']]));
});

// ---------------------------------------------------------------- Requests
test('signup keeps the password exactly as typed (old code HTML-escaped it before hashing)', function () use ($validator) {
    $password = 'P&ss"<word>';
    $dto = (new SignupRequest($validator))->parse([
        'first_name' => ' Ana ', 'last_name' => 'Ng', 'username' => 'ana', 'email' => 'a@b.co',
        'password' => $password, 'confirm_password' => $password,
    ]);
    same($password, $dto->password);
    same('Ana', $dto->firstName);
});

test('signup rejects mismatched confirmation on confirm_password', function () use ($validator) {
    $e = throws(ValidationException::class, fn () => (new SignupRequest($validator))->parse([
        'first_name' => 'A', 'last_name' => 'B', 'username' => 'abc', 'email' => 'a@b.co',
        'password' => 'secret1', 'confirm_password' => 'secret2',
    ]));
    same(['confirm_password'], array_keys($e->errors()));
});

test('tickets: negative counts are rejected (old code let adult=-5,child=2 through)', function () use ($validator) {
    $e = throws(ValidationException::class, fn () => (new BookTicketRequest($validator))
        ->parse(['date' => '2099-01-01', 'adult' => '-5', 'child' => '2'], TicketType::Standard));
    same(['adult'], array_keys($e->errors()));
});

test('tickets: zero total rejected, blank counts mean none', function () use ($validator) {
    $request = new BookTicketRequest($validator);
    $e = throws(ValidationException::class, fn () => $request->parse(['date' => '2099-01-01', 'adult' => '', 'child' => '0'], TicketType::Premium));
    same(['tickets'], array_keys($e->errors()));
    $ok = $request->parse(['date' => '2099-01-01', 'child' => '3'], TicketType::Premium);
    same([0, 3], [$ok->adult, $ok->child]);
});

test('accommodation: checkout must be after check-in', function () use ($validator) {
    $request = new AddAccommodationToCartRequest($validator);
    $base = ['id' => '3', 'guests' => '2'];
    throws(ValidationException::class, fn () => $request->parse($base + ['start_date' => '2099-05-05', 'end_date' => '2099-05-05']));
    $ok = $request->parse($base + ['start_date' => '2099-05-05', 'end_date' => '2099-05-08']);
    same(3, $ok->id);
});

// ---------------------------------------------------------------- Cart
test('cart total is derived and money converts to cents without truncation', function () {
    $cart = ticketCart(1, 0);            // 19.99
    same(19.99, $cart->total());
    same(1999, $cart->totalMinorUnits(), 'intval(19.99*100) would give 1998');
});

test('cart survives the session round trip and keeps the view-facing shape', function () {
    $cart = ticketCart()->with(stay());
    $array = $cart->toArray();
    same(['items', 'total'], array_keys($array));
    same('ticket', $array['items']['ticket_Standard_2030-01-15']['type']);
    same('accommodation', $array['items']['accommodation_7']['type']);
    same($array, Cart::fromArray($array)->toArray());
});

test('only one accommodation per cart; removing an unknown key fails loudly', function () {
    throws(CartException::class, fn () => Cart::empty()->with(stay(1))->with(stay(2)));
    throws(CartException::class, fn () => Cart::empty()->without('nope'));
});

test('removing an item recomputes the total instead of subtracting', function () {
    $cart = ticketCart()->with(stay());
    $after = $cart->without('accommodation_7');
    same(round(2 * 19.99 + 9.99, 2), $after->total());
});

test('unreadable session cart is discarded, not fatal', function () {
    $_SESSION = ['cart' => ['items' => ['x' => ['type' => 'mystery']]]];
    $writer = new MemoryLogWriter();
    $cart = (new SessionCartStore(new Logger($writer)))->load();
    same(true, $cart->isEmpty());
    same(false, isset($_SESSION['cart']));
    same(1, count($writer->lines));
});

test('ItemType maps to DB values and back to cart discriminators', function () {
    same('Ticket', ItemType::Ticket->value);
    same(ItemType::Accommodation, ItemType::tryFromCartType('accommodation'));
    same(null, ItemType::tryFromCartType('Ticket'));
});

// ---------------------------------------------------------------- Router / middleware / errors
final class EchoController
{
    public function hello(Request $r): Response
    {
        return Response::html('hi');
    }

    public function broken(Request $r): string
    {
        return 'not a response';
    }
}

$resolve = static function (string $class): object {
    return match ($class) {
        'Controllers\\Echo' => new EchoController(),
        'Middleware\\AuthMiddleware' => new AuthMiddleware(),
        default => throw new LogicException("unbound {$class}"),
    };
};

test('router matches exact method+path, normalises trailing slash, 404s otherwise', function () use ($resolve) {
    $router = (new Router($resolve))->get('/hi', 'Echo@hello');
    same('hi', $router->dispatch(new Request('GET', '/hi/'))->body());
    throws(NotFoundException::class, fn () => $router->dispatch(new Request('POST', '/hi')));
    throws(NotFoundException::class, fn () => $router->dispatch(new Request('GET', '/nope')));
});

test('router rejects bad handlers and duplicate routes at registration', function () use ($resolve) {
    throws(LogicException::class, fn () => (new Router($resolve))->get('/a', 'nope'));
    throws(LogicException::class, fn () => (new Router($resolve))->get('/a', 'Echo@hello')->get('/a', 'Echo@hello'));
});

test('a controller that returns a non-Response is a loud error', function () use ($resolve) {
    $router = (new Router($resolve))->get('/b', 'Echo@broken');
    throws(LogicException::class, fn () => $router->dispatch(new Request('GET', '/b')));
});

test('a mistyped middleware name is an error, not a silently skipped auth check', function () use ($resolve) {
    $router = (new Router($resolve))->get('/p', 'Echo@hello', ['AuthMidleware']);
    throws(LogicException::class, fn () => $router->dispatch(new Request('GET', '/p')));
});

test('a middleware that returns a Response short-circuits the controller', function () {
    $blocker = new class () implements Core\Middleware {
        public function handle(Request $request): ?Response
        {
            return Response::empty(HttpStatus::Forbidden);
        }
    };
    $router = (new Router(fn (string $c): object => match ($c) {
        'Middleware\\Blocker' => $blocker,
        default => new EchoController(),
    }))->get('/z', 'Echo@hello', ['Blocker']);
    same(403, $router->dispatch(new Request('GET', '/z'))->status()->value);
});

test('auth middleware blocks anonymous users', function () use ($resolve) {
    $_SESSION = [];
    $router = (new Router($resolve))->get('/p', 'Echo@hello', ['AuthMiddleware']);
    throws(AuthException::class, fn () => $router->dispatch(new Request('GET', '/p')));
    $_SESSION = ['customer_id' => 5, 'username' => 'u'];
    same('hi', $router->dispatch(new Request('GET', '/p'))->body());
});

$viewsDir = sys_get_temp_dir() . '/rza_views_' . bin2hex(random_bytes(4));
mkdir($viewsDir . '/errors', 0777, true);
file_put_contents($viewsDir . '/errors/404.php', '<p>404: <?= htmlspecialchars($message) ?></p>');
file_put_contents($viewsDir . '/hello.php', 'Hello <?= $name ?>');
$views = new ViewRenderer($viewsDir);
$logWriter = new MemoryLogWriter();
$handler = new ErrorHandler($views, new Logger($logWriter));
$serverWithReferer = ['HTTP_REFERER' => 'http://zoo.test/login?x=1', 'HTTP_HOST' => 'zoo.test'];

test('view renderer extracts data and does not leak output on failure', function () use ($views) {
    same('Hello Ana', $views->render('hello', ['name' => 'Ana'])->body());
    throws(LogicException::class, fn () => $views->render('missing'));
});

test('validation failure: flashes errors + old input WITHOUT passwords, redirects back', function () use ($handler, $serverWithReferer) {
    $_SESSION = [];
    $request = new Request('POST', '/login', ['username' => 'a', 'password' => 'hunter2', 'csrf_token' => 'z'], $serverWithReferer);
    $response = $handler->handle(fn () => throw new ValidationException(['username' => 'bad']), $request);
    same(302, $response->status()->value);
    same('/login?x=1', $response->headers()['Location']);
    same(['username' => 'bad'], $_SESSION['flash']['errors']);
    same(['username' => 'a'], $_SESSION['flash']['form_data']);
});

test('user-facing error: uses its own redirect, else back; JSON callers get JSON', function () use ($handler, $serverWithReferer) {
    $_SESSION = [];
    $withTarget = $handler->handle(fn () => throw new CartException('empty', '/cart'), new Request('POST', '/x', [], $serverWithReferer));
    same('/cart', $withTarget->headers()['Location']);
    same('empty', $_SESSION['flash']['error']);

    $back = $handler->handle(fn () => throw new CartException('nope'), new Request('POST', '/x', [], $serverWithReferer));
    same('/login?x=1', $back->headers()['Location']);

    $json = $handler->handle(fn () => throw new PaymentException('declined'), new Request('POST', '/x', [], ['HTTP_ACCEPT' => 'application/json']));
    same(402, $json->status()->value);
    same('{"error":"declined"}', $json->body());
});

test('not found renders the 404 view with status 404', function () use ($handler) {
    $response = $handler->handle(fn () => throw new NotFoundException('gone'), new Request('GET', '/x'));
    same(404, $response->status()->value);
    same('<p>404: gone</p>', $response->body());
});

test('unexpected exceptions are logged and never leak details', function () use ($handler, $logWriter) {
    $response = $handler->handle(fn () => throw new RuntimeException('secret db password'), new Request('GET', '/x'));
    same(500, $response->status()->value);
    same(false, str_contains($response->body(), 'secret'));
    same(true, str_contains(implode('', $logWriter->lines), 'secret db password'));
});

test('csrf exception is a 403 user-facing error', function () use ($handler) {
    $_SESSION = [];
    $r = $handler->handle(fn () => throw new CsrfTokenException(), new Request('POST', '/x', [], ['HTTP_ACCEPT' => 'application/json']));
    same(403, $r->status()->value);
});

test('backUrl never returns an off-site or protocol-relative target', function () {
    $req = fn (string $referer) => (new Request('POST', '/x', [], ['HTTP_REFERER' => $referer, 'HTTP_HOST' => 'zoo.test:8080']))->backUrl();
    same('/cart', $req('http://zoo.test:8080/cart'));
    same('/', $req('http://evil.test/cart'));
    same('/', $req('http://zoo.test//evil.test'));
    same('/', (new Request('POST', '/x'))->backUrl());
});

// ---------------------------------------------------------------- Checkout (payment-critical)
function checkout(FakeGateway $gateway, FakePlacer $placer): CheckoutService
{
    return new CheckoutService($gateway, $placer, new Logger(new MemoryLogWriter()));
}

function paid(int $customerId, int $amount): PaymentIntentState
{
    return new PaymentIntentState('pi_1', true, $amount, $customerId);
}

test('checkout: matching paid intent books the order once', function () {
    $g = new FakeGateway();
    $p = new FakePlacer();
    $cart = ticketCart();
    $g->state = paid(9, $cart->totalMinorUnits());
    same(42, checkout($g, $p)->complete(9, $cart, 'pi_1'));
    same([1, []], [$p->calls, $g->refunded]);
});

test('checkout: unpaid intent books nothing and refunds nothing', function () {
    $g = new FakeGateway();
    $p = new FakePlacer();
    $g->state = new PaymentIntentState('pi_1', false, 100, 9);
    throws(PaymentException::class, fn () => checkout($g, $p)->complete(9, ticketCart(), 'pi_1'));
    same([0, []], [$p->calls, $g->refunded]);
});

test('checkout: someone else\'s payment is never used and never refunded', function () {
    $g = new FakeGateway();
    $p = new FakePlacer();
    $cart = ticketCart();
    $g->state = paid(1, $cart->totalMinorUnits());
    throws(PaymentException::class, fn () => checkout($g, $p)->complete(9, $cart, 'pi_1'));
    same([0, []], [$p->calls, $g->refunded]);
});

test('checkout: cart changed after paying is refunded, not booked', function () {
    $g = new FakeGateway();
    $p = new FakePlacer();
    $g->state = paid(9, 100);
    throws(PaymentException::class, fn () => checkout($g, $p)->complete(9, ticketCart(), 'pi_1'));
    same([0, ['pi_1']], [$p->calls, $g->refunded]);
});

test('checkout: sold out after paying refunds and says so', function () {
    $g = new FakeGateway();
    $p = new FakePlacer();
    $p->failWith = new CartException('Not enough Standard Adult tickets remain');
    $cart = ticketCart();
    $g->state = paid(9, $cart->totalMinorUnits());
    $e = throws(PaymentException::class, fn () => checkout($g, $p)->complete(9, $cart, 'pi_1'));
    same(['pi_1'], $g->refunded);
    same(true, str_contains($e->getMessage(), 'refunded'));
    same('/cart', $e->redirectTo());
});

test('checkout: infrastructure errors are NOT refunded (safe to retry, order is idempotent)', function () {
    $g = new FakeGateway();
    $p = new FakePlacer();
    $p->failWith = new RuntimeException('db down');
    $cart = ticketCart();
    $g->state = paid(9, $cart->totalMinorUnits());
    throws(RuntimeException::class, fn () => checkout($g, $p)->complete(9, $cart, 'pi_1'));
    same([], $g->refunded);
});

test('checkout: failed refund tells the customer to contact support', function () {
    $g = new FakeGateway();
    $g->refundFails = true;
    $g->state = paid(9, 1);
    $e = throws(PaymentException::class, fn () => checkout($g, new FakePlacer())->complete(9, ticketCart(), 'pi_1'));
    same(true, str_contains($e->getMessage(), 'contact support'));
});

test('checkout: empty cart is rejected before touching the gateway', function () {
    throws(CartException::class, fn () => checkout(new FakeGateway(), new FakePlacer())->begin(9, Cart::empty()));
});

test('checkout: begin sends the cart total in cents', function () {
    $g = new class () extends FakeGateway {
        public int $amount = 0;

        public function createIntent(int $amountMinorUnits, string $currency, array $metadata): PaymentIntentRef
        {
            $this->amount = $amountMinorUnits;

            return parent::createIntent($amountMinorUnits, $currency, $metadata);
        }
    };
    checkout($g, new FakePlacer())->begin(9, ticketCart(1, 0));
    same(1999, $g->amount);
});

echo "\n{$GLOBALS['passed']} passed, {$GLOBALS['failed']} failed\n";
exit($GLOBALS['failed'] === 0 ? 0 : 1);

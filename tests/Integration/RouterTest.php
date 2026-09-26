<?php

declare(strict_types=1);

namespace Tests\Integration;

use Core\HttpStatus;
use Core\Middleware;
use Core\Request;
use Core\Response;
use Core\Router;
use Exceptions\AuthException;
use Middleware\AuthMiddleware;
use PHPUnit\Framework\TestCase;
use Tests\Support\EchoController;

/**
 * Router composed with real middleware — covers short-circuiting and the
 * AuthMiddleware session check end-to-end.
 */
final class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    private function authResolver(): callable
    {
        return static fn (string $class): object => match ($class) {
            'Controllers\\Echo' => new EchoController(),
            'Middleware\\AuthMiddleware' => new AuthMiddleware(),
            default => throw new \LogicException("unbound {$class}"),
        };
    }

    public function testAMiddlewareThatReturnsAResponseShortCircuitsTheController(): void
    {
        $blocker = new class () implements Middleware {
            public function handle(Request $request): ?Response
            {
                return Response::empty(HttpStatus::Forbidden);
            }
        };

        $router = (Router::withResolver(static fn (string $class): object => match ($class) {
            'Middleware\\Blocker' => $blocker,
            default => new EchoController(),
        }))->get('/z', 'Echo@hello', ['Blocker']);

        self::assertSame(403, $router->dispatch(new Request('GET', '/z'))->status()->value);
    }

    public function testAuthMiddlewareBlocksAnonymousUsers(): void
    {
        $router = (Router::withResolver($this->authResolver()))->get('/p', 'Echo@hello', ['AuthMiddleware']);

        $this->expectException(AuthException::class);
        $router->dispatch(new Request('GET', '/p'));
    }

    public function testAuthMiddlewareAllowsAuthenticatedUsersThrough(): void
    {
        $router = (Router::withResolver($this->authResolver()))->get('/p', 'Echo@hello', ['AuthMiddleware']);
        $_SESSION = ['customer_id' => 5, 'username' => 'u'];

        self::assertSame('hi', $router->dispatch(new Request('GET', '/p'))->body());
    }
}

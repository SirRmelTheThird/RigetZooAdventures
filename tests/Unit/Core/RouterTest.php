<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Core\Http\Request;
use Core\Http\Router;
use Core\Http\Middleware;
use Core\Http\Response;
use Exceptions\Http\NotFoundException;
use LogicException;
use PHPUnit\Framework\TestCase;
use Tests\Support\EchoController;

final class RouterTest extends TestCase
{
    private function resolver(): callable
    {
        return static fn (string $class): object => match ($class) {
            'Controllers\\Echo' => new EchoController(),
            default => throw new LogicException("unbound {$class}"),
        };
    }

    public function testMatchesExactMethodAndPathAndNormalisesTrailingSlash(): void
    {
        $router = (Router::withResolver($this->resolver()))->get('/hi', 'Echo@hello');

        self::assertSame('hi', $router->dispatch(new Request('GET', '/hi/'))->body());
    }

    public function testWrongMethodIs404(): void
    {
        $router = (Router::withResolver($this->resolver()))->get('/hi', 'Echo@hello');

        $this->expectException(NotFoundException::class);
        $router->dispatch(new Request('POST', '/hi'));
    }

    public function testUnknownPathIs404(): void
    {
        $router = (Router::withResolver($this->resolver()))->get('/hi', 'Echo@hello');

        $this->expectException(NotFoundException::class);
        $router->dispatch(new Request('GET', '/nope'));
    }

    public function testRejectsABadHandlerAtRegistration(): void
    {
        $this->expectException(LogicException::class);
        (Router::withResolver($this->resolver()))->get('/a', 'nope');
    }

    public function testRejectsADuplicateRouteAtRegistration(): void
    {
        $this->expectException(LogicException::class);
        (Router::withResolver($this->resolver()))->get('/a', 'Echo@hello')->get('/a', 'Echo@hello');
    }

    public function testAControllerThatReturnsANonResponseIsALoudError(): void
    {
        $router = (Router::withResolver($this->resolver()))->get('/b', 'Echo@broken');

        $this->expectException(LogicException::class);
        $router->dispatch(new Request('GET', '/b'));
    }

    public function testAMistypedMiddlewareNameIsAnError(): void
    {
        $router = (Router::withResolver($this->resolver()))->get('/p', 'Echo@hello', ['AuthMidleware']);

        $this->expectException(LogicException::class);
        $router->dispatch(new Request('GET', '/p'));
    }

    public function testConfiguredMiddlewareRunsInPipelineOrder(): void
    {
        $calls = [];
        $middleware = [
            'AuthMiddleware' => new class ($calls) implements Middleware {
                public function __construct(private array &$calls)
                {
                }

                public function handle(Request $request): ?Response
                {
                    $this->calls[] = 'auth';

                    return null;
                }
            },
            'CSRFMiddleware' => new class ($calls) implements Middleware {
                public function __construct(private array &$calls)
                {
                }

                public function handle(Request $request): ?Response
                {
                    $this->calls[] = 'csrf';

                    return null;
                }
            },
        ];
        $resolver = static function (string $class) use ($middleware): object {
            return match ($class) {
                'Controllers\\Echo' => new EchoController(),
                'Middleware\\AuthMiddleware' => $middleware['AuthMiddleware'],
                'Middleware\\CSRFMiddleware' => $middleware['CSRFMiddleware'],
                default => throw new LogicException("unbound {$class}"),
            };
        };

        $router = Router::withResolver($resolver)
            ->post('/p', 'Echo@hello', ['CSRFMiddleware', 'AuthMiddleware']);

        $router->dispatch(new Request('POST', '/p'));

        self::assertSame(['auth', 'csrf'], $calls);
    }
}

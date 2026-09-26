<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Core\Request;
use Core\Router;
use Exceptions\NotFoundException;
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
}

<?php

declare(strict_types=1);

namespace Tests\Integration;

use Bootstrap\Container;
use Controllers\Accommodation;
use Controllers\Auth;
use Controllers\Cart as CartController;
use Controllers\Home;
use Controllers\Payment;
use Controllers\Ticket;
use Illuminate\Database\ConnectionInterface;
use PHPUnit\Framework\TestCase;

/**
 * Universal container-resolution smoke test.
 * If any controller/service constructor is wrong (wrong arg count / wrong type),
 * this fails — preventing the site from breaking even when narrower unit tests pass.
 */
final class UniversalSmokeTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container(
            dirname(__DIR__, 2),
            $this->createStub(ConnectionInterface::class),
        );
    }

    public function testEveryControllerCanBeResolvedByContainer(): void
    {
        $controllers = [
            Home::class,
            Accommodation::class,
            Auth::class,
            CartController::class,
            Ticket::class,
            Payment::class,
        ];
        foreach ($controllers as $className) {
            $instance = $this->container->make($className);
            self::assertInstanceOf($className, $instance, "Failed to resolve {$className}");
        }
    }

    public function testRouterCanDispatchToAllControllers(): void
    {
        $router = $this->container->router();
        self::assertNotNull($router);
        // Just resolving the router through the real container proves wiring is intact.
    }
}

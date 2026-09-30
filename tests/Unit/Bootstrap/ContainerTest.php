<?php

declare(strict_types=1);

namespace Tests\Unit\Bootstrap;

use Bootstrap\Container;
use Bootstrap\ContainerException;
use Core\Error\ErrorHandler;
use Core\Http\Router;
use Illuminate\Database\ConnectionInterface;
use Middleware\AuthMiddleware;
use Middleware\CSRFMiddleware;
use PHPUnit\Framework\TestCase;

final class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container(dirname(__DIR__, 3));
    }

    public function testMakeThrowsContainerExceptionForUnregisteredClass(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessageIs('Nothing is registered for UnregisteredClass');

        $this->container->make('UnregisteredClass');
    }

    public function testMakeCreatesMiddlewareInstances(): void
    {
        $authMiddleware = $this->container->make(AuthMiddleware::class);
        $csrfMiddleware = $this->container->make(CSRFMiddleware::class);

        self::assertInstanceOf(AuthMiddleware::class, $authMiddleware);
        self::assertInstanceOf(CSRFMiddleware::class, $csrfMiddleware);
    }

    public function testRouterReturnsConfiguredRouter(): void
    {
        $router = $this->container->router();

        self::assertInstanceOf(Router::class, $router);
    }

    public function testContainerAcceptsAnExplicitDatabaseConnection(): void
    {
        $connection = $this->createStub(ConnectionInterface::class);
        $container = new Container(dirname(__DIR__, 3), $connection);

        self::assertSame($connection, $container->databaseConnection());
    }

    public function testErrorHandlerReturnsConfiguredErrorHandler(): void
    {
        $errorHandler = $this->container->errorHandler();

        self::assertInstanceOf(ErrorHandler::class, $errorHandler);
    }
}

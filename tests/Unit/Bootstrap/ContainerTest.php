<?php

declare(strict_types=1);

namespace Tests\Unit\Bootstrap;

use Bootstrap\Container;
use Bootstrap\ContainerException;
use Core\ErrorHandler;
use Core\Router;
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
        $this->expectExceptionMessage('Nothing is registered for UnregisteredClass');

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

    public function testErrorHandlerReturnsConfiguredErrorHandler(): void
    {
        $errorHandler = $this->container->errorHandler();

        self::assertInstanceOf(ErrorHandler::class, $errorHandler);
    }
}

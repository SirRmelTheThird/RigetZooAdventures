<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Core\ErrorHandler;
use Core\Logging\Logger;
use Core\Request;
use Core\ViewRenderer;
use Exceptions\CartException;
use Exceptions\CsrfTokenException;
use Exceptions\NotFoundException;
use Exceptions\PaymentException;
use Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Support\MemoryLogWriter;

final class ErrorHandlerTest extends TestCase
{
    private ErrorHandler $handler;
    private MemoryLogWriter $logWriter;
    private array $refererServer;
    private string $viewsDir;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->viewsDir = sys_get_temp_dir() . '/rza_views_' . bin2hex(random_bytes(4));
        mkdir($this->viewsDir . '/errors', 0777, true);
        file_put_contents($this->viewsDir . '/errors/404.php', '<p>404: <?= htmlspecialchars($message) ?></p>');

        $this->logWriter = new MemoryLogWriter();
        $this->handler = new ErrorHandler(new ViewRenderer($this->viewsDir), new Logger($this->logWriter));
        $this->refererServer = ['HTTP_REFERER' => 'http://zoo.test/login?x=1', 'HTTP_HOST' => 'zoo.test'];
    }

    protected function tearDown(): void
    {
        @unlink($this->viewsDir . '/errors/404.php');
        @rmdir($this->viewsDir . '/errors');
        @rmdir($this->viewsDir);
    }

    public function testValidationFailureFlashesErrorsAndOldInputWithoutPasswords(): void
    {
        $request = new Request('POST', '/login', ['username' => 'a', 'password' => 'hunter2', 'csrf_token' => 'z'], $this->refererServer);

        $response = $this->handler->handle(static fn () => throw new ValidationException(['username' => 'bad']), $request);

        self::assertSame(302, $response->status()->value);
        self::assertSame('/login?x=1', $response->headers()['Location']);
        self::assertSame(['username' => 'bad'], $_SESSION['flash']['errors']);
        self::assertSame(['username' => 'a'], $_SESSION['flash']['form_data']);
    }

    public function testUserFacingErrorUsesItsOwnRedirectElseFallsBack(): void
    {
        $withTarget = $this->handler->handle(
            static fn () => throw new CartException('empty', '/cart'),
            new Request('POST', '/x', [], $this->refererServer)
        );

        self::assertSame('/cart', $withTarget->headers()['Location']);
        self::assertSame('empty', $_SESSION['flash']['error']);

        $back = $this->handler->handle(
            static fn () => throw new CartException('nope'),
            new Request('POST', '/x', [], $this->refererServer)
        );

        self::assertSame('/login?x=1', $back->headers()['Location']);
    }

    public function testJsonCallersGetJsonForUserFacingErrors(): void
    {
        $json = $this->handler->handle(
            static fn () => throw new PaymentException('declined'),
            new Request('POST', '/x', [], ['HTTP_ACCEPT' => 'application/json'])
        );

        self::assertSame(402, $json->status()->value);
        self::assertSame('{"error":"declined"}', $json->body());
    }

    public function testNotFoundRendersThe404ViewWithStatus404(): void
    {
        $response = $this->handler->handle(static fn () => throw new NotFoundException('gone'), new Request('GET', '/x'));

        self::assertSame(404, $response->status()->value);
        self::assertSame('<p>404: gone</p>', $response->body());
    }

    public function testUnexpectedExceptionsAreLoggedAndNeverLeakDetails(): void
    {
        $response = $this->handler->handle(static fn () => throw new RuntimeException('secret db password'), new Request('GET', '/x'));

        self::assertSame(500, $response->status()->value);
        self::assertStringNotContainsString('secret', $response->body());
        self::assertStringContainsString('secret db password', implode('', $this->logWriter->lines));
    }

    public function testCsrfExceptionIsA403UserFacingError(): void
    {
        $response = $this->handler->handle(
            static fn () => throw new CsrfTokenException(),
            new Request('POST', '/x', [], ['HTTP_ACCEPT' => 'application/json'])
        );

        self::assertSame(403, $response->status()->value);
    }
}

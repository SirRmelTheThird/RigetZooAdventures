<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use Core\Constants\SessionKey;
use Core\Http\Request;
use Exceptions\Http\CsrfTokenException;
use Middleware\CSRFMiddleware;
use PHPUnit\Framework\TestCase;

final class CSRFMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testGetRequestsBypassCsrfValidation(): void
    {
        self::assertNull((new CSRFMiddleware())->handle(new Request('GET', '/')));
    }

    public function testValidPostTokenPasses(): void
    {
        $_SESSION[SessionKey::CSRF_TOKEN] = 'token';

        self::assertNull((new CSRFMiddleware())->handle(
            new Request('POST', '/login', [SessionKey::CSRF_TOKEN => 'token'])
        ));
    }

    public function testMissingPostTokenIsRejected(): void
    {
        $this->expectException(CsrfTokenException::class);

        (new CSRFMiddleware())->handle(new Request('POST', '/login'));
    }

    public function testInvalidPostTokenIsRejected(): void
    {
        $_SESSION[SessionKey::CSRF_TOKEN] = 'expected';
        $this->expectException(CsrfTokenException::class);

        (new CSRFMiddleware())->handle(
            new Request('POST', '/login', [SessionKey::CSRF_TOKEN => 'wrong'])
        );
    }
}

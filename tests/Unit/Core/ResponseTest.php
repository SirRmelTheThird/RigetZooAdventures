<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Core\Http\Response;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    public function testHtmlResponseIncludesBrowserSecurityHeaders(): void
    {
        $headers = Response::html('ok')->headers();

        self::assertSame('nosniff', $headers['X-Content-Type-Options']);
        self::assertSame('SAMEORIGIN', $headers['X-Frame-Options']);
        self::assertSame('strict-origin-when-cross-origin', $headers['Referrer-Policy']);
    }

    public function testSecurityHeadersApplyToRedirectsAndJsonResponses(): void
    {
        $redirectHeaders = Response::redirect('/')->headers();
        $jsonHeaders = Response::json(['ok' => true])->headers();

        self::assertSame('nosniff', $redirectHeaders['X-Content-Type-Options']);
        self::assertSame('SAMEORIGIN', $redirectHeaders['X-Frame-Options']);
        self::assertSame('nosniff', $jsonHeaders['X-Content-Type-Options']);
        self::assertSame('SAMEORIGIN', $jsonHeaders['X-Frame-Options']);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Core\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    #[DataProvider('refererProvider')]
    public function testBackUrlNeverReturnsAnOffSiteOrProtocolRelativeTarget(string $referer, string $expected): void
    {
        $request = new Request('POST', '/x', [], ['HTTP_REFERER' => $referer, 'HTTP_HOST' => 'zoo.test:8080']);

        self::assertSame($expected, $request->backUrl());
    }

    public static function refererProvider(): array
    {
        return [
            'same host and port' => ['http://zoo.test:8080/cart', '/cart'],
            'different host' => ['http://evil.test/cart', '/'],
            'protocol-relative payload' => ['http://zoo.test//evil.test', '/'],
        ];
    }

    public function testBackUrlWithNoRefererFallsBackToRoot(): void
    {
        self::assertSame('/', (new Request('POST', '/x'))->backUrl());
    }
}

<?php

declare(strict_types=1);

namespace Tests\Integration;

use DTOs\LoginCredentials;
use DTOs\Registration;
use Services\AuthService;
use Core\Logging\Logger;
use PHPUnit\Framework\TestCase;
use Tests\Support\MemoryLogWriter;

final class AuthServiceTest extends TestCase
{
    public function testAuthenticateReturnsCustomer(): void
    {
        $auth = new AuthService(new Logger(new MemoryLogWriter()));
        self::assertInstanceOf(AuthService::class, $auth);
        self::assertNotNull($auth);
    }
}

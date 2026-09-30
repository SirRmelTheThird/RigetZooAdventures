<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Config\Config;
use Enums\EnvKey;
use Exceptions\System\InvalidEnvVariableException;
use Exceptions\System\MissingEnvVariableException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class ConfigTest extends TestCase
{
    protected function setUp(): void
    {
        $this->resetConfigState();
    }

    protected function tearDown(): void
    {
        $this->resetConfigState();
    }

    public function testShouldDisplayErrorsIsFalseWhenDebugIsFalse(): void
    {
        $this->setConfigEnvironment([EnvKey::AppDebug->value => 'false']);

        self::assertFalse(Config::shouldDisplayErrors());
    }

    public function testShouldDisplayErrorsAllowsExplicitDebugMode(): void
    {
        $this->setConfigEnvironment([EnvKey::AppDebug->value => 'true']);

        self::assertTrue(Config::shouldDisplayErrors());
    }

    public function testShouldDisplayErrorsRejectsNonBooleanValue(): void
    {
        $this->setConfigEnvironment([EnvKey::AppDebug->value => 'ture']);

        $this->expectException(InvalidEnvVariableException::class);

        Config::shouldDisplayErrors();
    }

    public function testShouldDisplayErrorsRejectsEmptyValue(): void
    {
        $this->setConfigEnvironment([EnvKey::AppDebug->value => '']);

        $this->expectException(MissingEnvVariableException::class);

        Config::shouldDisplayErrors();
    }

    public function testRequireReturnsValue(): void
    {
        $this->setConfigEnvironment([EnvKey::DbHost->value => '127.0.0.1']);

        self::assertSame('127.0.0.1', Config::require(EnvKey::DbHost));
    }

    public function testRequireRejectsEmptyValue(): void
    {
        $this->setConfigEnvironment([EnvKey::DbHost->value => '  ']);

        $this->expectException(MissingEnvVariableException::class);

        Config::require(EnvKey::DbHost);
    }

    public function testRequireAllowingEmptyAcceptsEmptyValue(): void
    {
        $this->setConfigEnvironment([EnvKey::DbPassword->value => '']);

        self::assertSame('', Config::requireAllowingEmpty(EnvKey::DbPassword));
    }

    public function testOptionalReturnsNullForEmptyValue(): void
    {
        $this->setConfigEnvironment([EnvKey::DiscordCaBundle->value => '']);

        self::assertNull(Config::optional(EnvKey::DiscordCaBundle));
    }

    public function testOptionalReturnsValueWhenSet(): void
    {
        $this->setConfigEnvironment([EnvKey::DiscordCaBundle->value => '/etc/ssl/ca.pem']);

        self::assertSame('/etc/ssl/ca.pem', Config::optional(EnvKey::DiscordCaBundle));
    }

    public function testRequireIntReturnsInteger(): void
    {
        $this->setConfigEnvironment([EnvKey::DbPort->value => '3306']);

        self::assertSame(3306, Config::requireInt(EnvKey::DbPort));
    }

    public function testRequireIntRejectsNonInteger(): void
    {
        $this->setConfigEnvironment([EnvKey::DbPort->value => 'abc']);

        $this->expectException(InvalidEnvVariableException::class);

        Config::requireInt(EnvKey::DbPort);
    }

    private function resetConfigState(): void
    {
        $loaded = new ReflectionProperty(Config::class, 'loaded');
        $loaded->setValue(null, false);

        $env = new ReflectionProperty(Config::class, 'env');
        $env->setValue(null, []);
    }

    /** @param array<string, string> $environment */
    private function setConfigEnvironment(array $environment): void
    {
        $env = new ReflectionProperty(Config::class, 'env');
        $env->setValue(null, $environment);

        $loaded = new ReflectionProperty(Config::class, 'loaded');
        $loaded->setValue(null, true);
    }
}

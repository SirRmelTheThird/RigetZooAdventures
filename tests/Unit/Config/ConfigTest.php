<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Config\Config;
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

    private function resetConfigState(): void
    {
        $loaded = new ReflectionProperty(Config::class, 'loaded');
        $loaded->setValue(null, false);

        $env = new ReflectionProperty(Config::class, 'env');
        $env->setValue(null, []);

        $_ENV = [];
    }

    public function testShouldDisplayErrorsDefaultsToFalseInProduction(): void
    {
        $this->setConfigEnvironment(['APP_DEBUG' => 'false']);

        self::assertFalse(Config::shouldDisplayErrors());
    }

    public function testShouldDisplayErrorsAllowsExplicitDebugMode(): void
    {
        $this->setConfigEnvironment(['APP_DEBUG' => 'true']);

        self::assertTrue(Config::shouldDisplayErrors());
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

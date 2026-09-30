<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Core\Constants\SessionKey;
use PHPUnit\Framework\TestCase;
use Services\Views\SharedViewData;
use Tests\Support\InMemorySessionStore;

final class SharedViewDataTest extends TestCase
{
    public function testIsLoggedInAndUsername(): void
    {
        $session = new InMemorySessionStore();
        $shared = new SharedViewData($session);

        self::assertFalse($shared->isLoggedIn());
        self::assertNull($shared->username());

        $session->signIn('cust-123', 'johndoe', 'John', 'john@example.com');

        self::assertTrue($shared->isLoggedIn());
        self::assertSame('johndoe', $shared->username());
    }

    public function testCartCount(): void
    {
        $session = new InMemorySessionStore(['cart_count' => 5]);
        $shared = new SharedViewData($session);

        self::assertSame(5, $shared->cartCount());
    }

    public function testGetFlashAndHelpers(): void
    {
        $session = new InMemorySessionStore([], [
            'custom' => 'custom_val',
            SessionKey::SUCCESS => 'Operation successful',
            SessionKey::ERROR => 'Something went wrong',
            SessionKey::VALIDATION_ERRORS => ['Field is required'],
            SessionKey::FORM_DATA => ['username' => 'alice'],
        ]);
        $shared = new SharedViewData($session);

        self::assertSame('custom_val', $shared->getFlash('custom'));
        self::assertSame('default_val', $shared->getFlash('missing', 'default_val'));
        self::assertSame('Operation successful', $shared->getSuccessFlash());
        self::assertSame('Something went wrong', $shared->getErrorFlash());
        self::assertSame(['Field is required'], $shared->getValidationErrors());

        $old = $shared->oldInput();
        self::assertSame('alice', $old->get('username'));
        self::assertSame('', $old->get('missing'));
    }

    public function testGetFlashReturnsNullWhenNotSetOrInvalidType(): void
    {
        $session = new InMemorySessionStore([], [
            SessionKey::SUCCESS => ['not a string'],
            SessionKey::ERROR => 123,
            SessionKey::VALIDATION_ERRORS => 'not an array',
        ]);
        $shared = new SharedViewData($session);

        self::assertNull($shared->getSuccessFlash());
        self::assertNull($shared->getErrorFlash());
        self::assertNull($shared->getValidationErrors());
    }
}

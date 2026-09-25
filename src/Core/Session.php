<?php

declare(strict_types=1);

namespace Core;

use Core\Constants\SessionKey;
use Exceptions\AuthException;

final class Session
{
    private const LIFETIME_SECONDS = 1800;
    private const COOKIE_PATH = '/';
    private const COOKIE_SAMESITE = 'Lax';
    private const INI_USE_ONLY_COOKIES = 'session.use_only_cookies';
    private const INI_USE_STRICT_MODE = 'session.use_strict_mode';

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set(self::INI_USE_ONLY_COOKIES, '1');
        ini_set(self::INI_USE_STRICT_MODE, '1');

        session_set_cookie_params([
            'lifetime' => self::LIFETIME_SECONDS,
            'path' => self::COOKIE_PATH,
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => self::COOKIE_SAMESITE,
        ]);

        session_start();

        self::regenerateIfDue();
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION[SessionKey::LAST_REGENERATION] = time();
    }

    public static function invalidate(): void
    {
        $_SESSION = [];
        self::regenerate();
    }

    public static function signIn(int $customerId, string $username, string $firstName, string $email): void
    {
        self::regenerate();

        $_SESSION[SessionKey::CUSTOMER_ID] = $customerId;
        $_SESSION[SessionKey::USERNAME] = $username;
        $_SESSION[SessionKey::FIRST_NAME] = $firstName;
        $_SESSION[SessionKey::EMAIL] = $email;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (!isset($_SESSION[$key])) {
            return $default;
        }

        return $_SESSION[$key];
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION[SessionKey::FLASH][$key] = $value;
    }

    public static function flashSuccess(string $message): void
    {
        self::flash(SessionKey::SUCCESS, $message);
    }

    public static function flashError(string $message): void
    {
        self::flash(SessionKey::ERROR, $message);
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        if (!isset($_SESSION[SessionKey::FLASH][$key])) {
            return $default;
        }

        $value = $_SESSION[SessionKey::FLASH][$key];
        unset($_SESSION[SessionKey::FLASH][$key]);

        return $value;
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION[SessionKey::CUSTOMER_ID], $_SESSION[SessionKey::USERNAME]);
    }


    public static function userId(): int
    {
        if (!self::isLoggedIn()) {
            throw AuthException::loginRequired();
        }

        return (int) $_SESSION[SessionKey::CUSTOMER_ID];
    }

    public static function getUserId(): ?int
    {
        if (!isset($_SESSION[SessionKey::CUSTOMER_ID])) {
            return null;
        }

        return (int) $_SESSION[SessionKey::CUSTOMER_ID];
    }

    public static function getUsername(): ?string
    {
        if (!isset($_SESSION[SessionKey::USERNAME])) {
            return null;
        }

        return (string) $_SESSION[SessionKey::USERNAME];
    }

    private static function isHttps(): bool
    {
        if (!isset($_SERVER['HTTPS'])) {
            return false;
        }

        return $_SERVER['HTTPS'] === 'on';
    }

    private static function regenerateIfDue(): void
    {
        if (!isset($_SESSION[SessionKey::LAST_REGENERATION])) {
            self::regenerate();

            return;
        }

        if (time() - (int) $_SESSION[SessionKey::LAST_REGENERATION] >= self::LIFETIME_SECONDS) {
            self::regenerate();
        }
    }
}

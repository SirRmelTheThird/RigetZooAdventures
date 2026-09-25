<?php

declare(strict_types=1);

namespace Core;

use Core\Constants\SessionKey;

final class CSRF
{
    public const FIELD_NAME = 'csrf_token';
    private const TOKEN_BYTES = 32;

    public static function generate(): string
    {
        if (!Session::has(SessionKey::CSRF_TOKEN)) {
            Session::set(SessionKey::CSRF_TOKEN, bin2hex(random_bytes(self::TOKEN_BYTES)));
        }

        return (string) Session::get(SessionKey::CSRF_TOKEN);
    }

    public static function validate(mixed $token): bool
    {
        if (!is_string($token)) {
            return false;
        }

        if (!Session::has(SessionKey::CSRF_TOKEN)) {
            return false;
        }

        return hash_equals((string) Session::get(SessionKey::CSRF_TOKEN), $token);
    }

    public static function field(): string
    {
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::FIELD_NAME,
            htmlspecialchars(self::generate(), ENT_QUOTES, 'UTF-8'),
        );
    }
}

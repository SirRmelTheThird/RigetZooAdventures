<?php

declare(strict_types=1);

namespace Middleware;

use Core\Http\Middleware;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;
use Exceptions\Auth\AuthException;

final class AuthMiddleware implements Middleware
{
    public function handle(Request $request): ?Response
    {
        if (!Session::isLoggedIn()) {
            throw AuthException::loginRequired();
        }

        return null;
    }
}

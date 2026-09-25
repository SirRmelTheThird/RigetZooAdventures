<?php

declare(strict_types=1);

namespace Middleware;

use Core\Middleware;
use Core\Request;
use Core\Response;
use Core\Session;
use Exceptions\AuthException;

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

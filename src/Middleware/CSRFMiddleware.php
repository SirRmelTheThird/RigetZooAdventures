<?php

declare(strict_types=1);

namespace Middleware;

use Core\Security\CSRF;
use Core\Http\Middleware;
use Core\Http\Request;
use Core\Http\Response;
use Exceptions\Http\CsrfTokenException;

final class CSRFMiddleware implements Middleware
{
    public function handle(Request $request): ?Response
    {
        if (!$request->isPost()) {
            return null;
        }

        $token = null;

        if (array_key_exists(CSRF::FIELD_NAME, $request->body())) {
            $token = $request->body()[CSRF::FIELD_NAME];
        }

        if (!CSRF::validate($token)) {
            throw new CsrfTokenException();
        }

        return null;
    }
}

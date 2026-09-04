<?php

namespace Middleware;

use Core\Response;
use Core\CSRF;

class CSRFMiddleware
{
    public function handle()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';

            if (!CSRF::validate($token)) {
                Response::json(['error' => 'CSRF token invalid'], 403);
            }
        }
    }
}

<?php

namespace Middleware;

use Core\Response;
use Core\Session;

class AuthMiddleware
{
    public function handle()
    {
        if (!Session::isLoggedIn()) {
            Session::flash('error', 'Please log in to continue');
            Response::redirect('/login');
        }
    }
}

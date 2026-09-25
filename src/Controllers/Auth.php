<?php

declare(strict_types=1);

namespace Controllers;

use Core\Constants\RedirectKey;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\ViewRenderer;
use Requests\LoginRequest;
use Requests\SignupRequest;
use Services\AuthService;
use Support\Messages;

final class Auth
{
    public function __construct(
        private readonly ViewRenderer $views,
        private readonly AuthService $auth,
        private readonly LoginRequest $loginRequest,
        private readonly SignupRequest $signupRequest,
    ) {
    }

    public function showLogin(Request $request): Response
    {
        return $this->views->render('auth/login');
    }

    public function login(Request $request): Response
    {
        $customer = $this->auth->authenticate($this->loginRequest->parse($request->body()));

        Session::signIn((int) $customer->id, (string) $customer->username, (string) $customer->first_name, (string) $customer->email);
        Session::flashSuccess(Messages::LOGGED_IN);

        return Response::redirect(RedirectKey::HOME);
    }

    public function showSignup(Request $request): Response
    {
        return $this->views->render('auth/signup');
    }

    public function signup(Request $request): Response
    {
        $this->auth->register($this->signupRequest->parse($request->body()));

        Session::flashSuccess(Messages::ACCOUNT_CREATED);

        return Response::redirect(RedirectKey::LOGIN);
    }

    public function logout(Request $request): Response
    {
        Session::invalidate();
        Session::flashSuccess(Messages::LOGGED_OUT);

        return Response::redirect(RedirectKey::HOME);
    }
}
